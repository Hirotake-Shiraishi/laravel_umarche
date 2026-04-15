<?php

/**
 * Stripe Webhook まわりの Feature テスト
 *
 * 【テストの考え方】
 * - 本番では Stripe のサーバーが署名付きで POST してくる。テストでは同じアルゴリズム（timestamp + payload の HMAC）で
 *   Stripe-Signature ヘッダを自前で作り、Webhook::constructEvent が通るリクエストを再現している。
 * - phpunit.xml の STRIPE_WEBHOOK_SECRET と、signPayload() が使う secret が一致している必要がある。
 *
 * 【各テストの意図】
 * - 署名ヘッダ欠落 → 400（計画フェーズ C）
 * - Webhook シークレット未設定 → 500（計画フェーズ C）
 * - 署名が不正 → 400
 * - checkout.session.completed 正常 → 在庫減算・orders / order_items 作成・メールジョブ dispatch・カート削除
 * - 同一セッションのペイロードを2回 → 冪等（二重在庫減算・二重メールにならない）
 * - 在庫不足 → 在庫不変（返金はテストではシークレット未設定によりスキップされうる）
 */

namespace Tests\Feature;

use App\Jobs\SendOrderedMail;
use App\Jobs\SendThanksMail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    /** config / phpunit で渡した Webhook 署名用シークレット（Stripe ダッシュボードの whsec_... に相当するテスト用値） */
    private function webhookSecret(): string
    {
        return (string) config('services.stripe.webhook_secret');
    }

    /**
     * Stripe PHP SDK（WebhookSignature）と同じ規則で署名ヘッダを生成する
     * 形式: t=UNIX時刻,v1=HMAC_SHA256(secret, t + '.' + rawPayload)
     */
    private function signPayload(string $payload): string
    {
        $timestamp = time();
        $signedPayload = $timestamp . '.' . $payload;
        $signature = hash_hmac('sha256', $signedPayload, $this->webhookSecret());

        return 't=' . $timestamp . ',v1=' . $signature;
    }

    /** products テーブルが参照する shop / category / image を最低限 INSERT（Factory だけでは FK が足りないため） */
    protected function seedItemListDependencies(): void
    {
        $now = now();
        DB::table('owners')->insert([
            ['id' => 1, 'name' => 'owner1', 'email' => 'owner1@example.com', 'password' => Hash::make('password'), 'created_at' => $now, 'updated_at' => $now],
        ]);
        DB::table('primary_categories')->insert([
            ['id' => 1, 'name' => 'キッズ', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
        DB::table('secondary_categories')->insert([
            ['id' => 1, 'name' => '靴', 'sort_order' => 1, 'primary_category_id' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
        DB::table('shops')->insert([
            ['id' => 1, 'owner_id' => 1, 'name' => 'テスト店', 'information' => '説明', 'filename' => 'sample1.jpg', 'is_selling' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
        DB::table('images')->insert([
            ['id' => 1, 'owner_id' => 1, 'filename' => 'sample1.jpg', 'title' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /** Stripe が送る JSON の最小形。type が checkout.session.completed であることが重要。 */
    private function buildCheckoutSessionCompletedPayload(string $sessionId, int $userId): string
    {
        return json_encode([
            'id' => 'evt_test_' . $sessionId,
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => $sessionId,
                    'object' => 'checkout.session',
                    'client_reference_id' => (string) $userId,
                    'payment_intent' => 'pi_test_' . $sessionId,
                ],
            ],
        ]);
    }

    public function test_missing_stripe_signature_returns_400(): void
    {
        $payload = $this->buildCheckoutSessionCompletedPayload('cs_test_sig', 1);

        $response = $this->call(
            'POST',
            '/api/webhook/stripe',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $response->assertStatus(400);
        $response->assertSee('Missing Stripe-Signature', false);
    }

    public function test_webhook_secret_not_configured_returns_500(): void
    {
        $original = config('services.stripe.webhook_secret');
        Config::set('services.stripe.webhook_secret', '');

        try {
            $payload = $this->buildCheckoutSessionCompletedPayload('cs_test_nosecret', 1);
            $response = $this->call(
                'POST',
                '/api/webhook/stripe',
                [],
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_STRIPE_SIGNATURE' => $this->signPayload($payload),
                ],
                $payload
            );

            $response->assertStatus(500);
        } finally {
            Config::set('services.stripe.webhook_secret', $original);
        }
    }

    public function test_invalid_signature_returns_400(): void
    {
        $payload = '{"id":"evt_x","object":"event","type":"checkout.session.completed","data":{"object":{}}}';

        $response = $this->call(
            'POST',
            '/api/webhook/stripe',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => 't=' . time() . ',v1=invalid'],
            $payload
        );

        $response->assertStatus(400);
    }

    public function test_non_checkout_event_returns_200_without_fulfillment(): void
    {
        $this->seedItemListDependencies();
        Bus::fake();

        $user = User::factory()->create();
        $product = Product::create([
            'shop_id' => 1,
            'name' => '無視される商品',
            'information' => '説明',
            'price' => 1000,
            'is_selling' => true,
            'sort_order' => 1,
            'secondary_category_id' => 1,
            'image1' => 1,
            'image2' => 1,
            'image3' => 1,
            'image4' => 1,
        ]);
        Stock::create([
            'product_id' => $product->id,
            'type' => 1,
            'quantity' => 5,
        ]);
        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $payload = json_encode([
            'id' => 'evt_other_1',
            'object' => 'event',
            'type' => 'customer.created',
            'data' => ['object' => ['id' => 'cus_test']],
        ]);

        $response = $this->call(
            'POST',
            '/api/webhook/stripe',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => $this->signPayload($payload),
            ],
            $payload
        );

        $response->assertStatus(200);
        Bus::assertNothingDispatched();
        $this->assertSame(1, Cart::where('user_id', $user->id)->count());
        $this->assertSame(5, (int) Stock::where('product_id', $product->id)->sum('quantity'));
    }

    public function test_valid_webhook_decrements_stock_creates_order_dispatches_mail_and_clears_cart(): void
    {
        Bus::fake();
        $this->seedItemListDependencies();

        $user = User::factory()->create();
        $product = Product::create([
            'shop_id' => 1,
            'name' => 'Webhook商品',
            'information' => '説明',
            'price' => 1000,
            'is_selling' => true,
            'sort_order' => 1,
            'secondary_category_id' => 1,
            'image1' => 1,
            'image2' => 1,
            'image3' => 1,
            'image4' => 1,
        ]);
        Stock::create([
            'product_id' => $product->id,
            'type' => 1,
            'quantity' => 5,
        ]);
        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $sessionId = 'cs_test_' . uniqid();
        $payload = $this->buildCheckoutSessionCompletedPayload($sessionId, $user->id);

        $response = $this->call(
            'POST',
            '/api/webhook/stripe',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => $this->signPayload($payload),
            ],
            $payload
        );

        $response->assertStatus(200);
        $this->assertSame(0, Cart::where('user_id', $user->id)->count());
        $this->assertSame(4, (int) Stock::where('product_id', $product->id)->sum('quantity'));

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'stripe_session_id' => $sessionId,
            'total_price' => 1000,
        ]);

        Bus::assertDispatched(SendThanksMail::class);
        Bus::assertDispatched(SendOrderedMail::class);
    }

    public function test_duplicate_webhook_is_idempotent(): void
    {
        Bus::fake();
        $this->seedItemListDependencies();

        $user = User::factory()->create();
        $product = Product::create([
            'shop_id' => 1,
            'name' => 'Webhook商品2',
            'information' => '説明',
            'price' => 500,
            'is_selling' => true,
            'sort_order' => 1,
            'secondary_category_id' => 1,
            'image1' => 1,
            'image2' => 1,
            'image3' => 1,
            'image4' => 1,
        ]);
        Stock::create([
            'product_id' => $product->id,
            'type' => 1,
            'quantity' => 3,
        ]);
        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $sessionId = 'cs_test_dup_' . uniqid();
        $payload = $this->buildCheckoutSessionCompletedPayload($sessionId, $user->id);
        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => $this->signPayload($payload),
        ];

        $this->call('POST', '/api/webhook/stripe', [], [], [], $headers, $payload)->assertStatus(200);
        $this->call('POST', '/api/webhook/stripe', [], [], [], $headers, $payload)->assertStatus(200);

        $this->assertSame(0, Cart::where('user_id', $user->id)->count());
        $this->assertSame(2, (int) Stock::where('product_id', $product->id)->sum('quantity'));
        $this->assertSame(1, Order::where('stripe_session_id', $sessionId)->count());

        Bus::assertDispatchedTimes(SendThanksMail::class, 1);
        Bus::assertDispatchedTimes(SendOrderedMail::class, 1);
    }

    public function test_webhook_insufficient_stock_does_not_change_stock(): void
    {
        Bus::fake();
        $this->seedItemListDependencies();

        $user = User::factory()->create();
        $product = Product::create([
            'shop_id' => 1,
            'name' => '在庫1',
            'information' => '説明',
            'price' => 100,
            'is_selling' => true,
            'sort_order' => 1,
            'secondary_category_id' => 1,
            'image1' => 1,
            'image2' => 1,
            'image3' => 1,
            'image4' => 1,
        ]);
        Stock::create([
            'product_id' => $product->id,
            'type' => 1,
            'quantity' => 1,
        ]);
        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $sessionId = 'cs_test_nostock_' . uniqid();
        $payload = $this->buildCheckoutSessionCompletedPayload($sessionId, $user->id);

        $response = $this->call(
            'POST',
            '/api/webhook/stripe',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => $this->signPayload($payload),
            ],
            $payload
        );

        $response->assertStatus(200);
        $this->assertSame(1, (int) Stock::where('product_id', $product->id)->sum('quantity'));
        $this->assertSame(0, Order::where('stripe_session_id', $sessionId)->count());
        Bus::assertNothingDispatched();
    }
}
