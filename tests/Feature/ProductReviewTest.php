<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Owner;
use App\Models\Product;
use App\Models\Review;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * 商品レビュー（課題5）Feature テスト
 *
 * 【テストの目的】
 * - 購入者のみレビュー投稿できる（認可）
 * - 1商品につき1ユーザー1回のみ投稿できる（ユニーク制約 + 事前チェック）
 * - 詳細画面でレビューが見える（show の eager load が効いている前提）
 * - 非掲載（availableItems 外）でも購入済みなら詳細・投稿可／未購入の他人は 404
 * - オーナー別にレビュー一覧が自店舗分のみになること
 *
 * ※ 本番では購入データ（orders/order_items）は Stripe Webhook で作られるが、
 *    Feature テストでは DB に直接 orders/order_items を作り「購入済み」を再現する。
 */
class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 商品一覧/詳細（availableItems）に必要な依存テーブルをシードする
     *
     * Product::availableItems() は JOIN で shops / secondary_categories / images を参照するため、
     * それらが無いとテストで商品作成や表示が失敗しやすい。
     */
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

    /** テスト用の「掲載中」商品を1つ作成 */
    protected function createAvailableProduct(): Product
    {
        $product = Product::create([
            'shop_id' => 1,
            'name' => 'レビュー用商品',
            'information' => 'テスト説明',
            'price' => 1000,
            'is_selling' => true,
            'sort_order' => 1,
            'secondary_category_id' => 1,
            'image1' => 1,
            'image2' => 1,
            'image3' => 1,
            'image4' => 1,
        ]);

        // availableItems() は t_stocks の sum(quantity) > 0 が必要
        Stock::create([
            'product_id' => $product->id,
            'type' => 1,
            'quantity' => 1,
        ]);

        return $product;
    }

    /**
     * availableItems に載らない商品（在庫なし = サブクエリに含まれない）
     *
     * 非掲載でも order_items があれば購入者は詳細・レビュー投稿にアクセスできる仕様の検証用。
     */
    protected function createUnavailableProduct(): Product
    {
        return Product::create([
            'shop_id' => 1,
            'name' => '非掲載レビュー用商品',
            'information' => 'テスト説明',
            'price' => 2000,
            'is_selling' => true,
            'sort_order' => 2,
            'secondary_category_id' => 1,
            'image1' => 1,
            'image2' => 1,
            'image3' => 1,
            'image4' => 1,
        ]);
    }

    /** seedItemListDependencies に加え、2店舗目のオーナー・ショップを追加する */
    protected function seedSecondShop(): void
    {
        $now = now();
        DB::table('owners')->insert([
            [
                'id' => 2,
                'name' => 'owner2',
                'email' => 'owner2_review_test@example.com',
                'password' => Hash::make('password'),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
        DB::table('shops')->insert([
            [
                'id' => 2,
                'owner_id' => 2,
                'name' => 'レビューテスト店舗B',
                'information' => '説明',
                'filename' => 'sample1.jpg',
                'is_selling' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /** 指定ショップの掲載可能商品を1つ作成（オーナー一覧のレビュー紐付け用） */
    protected function createAvailableProductForShop(int $shopId): Product
    {
        $product = Product::create([
            'shop_id' => $shopId,
            'name' => 'レビュー用商品ショップ' . $shopId,
            'information' => 'テスト説明',
            'price' => 1500,
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

        return $product;
    }

    /** 指定ユーザーが指定商品を購入したことにする（orders/order_items を作成） */
    protected function markPurchased(User $user, Product $product): void
    {
        $order = Order::create([
            'user_id' => $user->id,
            'stripe_session_id' => 'cs_test_review_' . uniqid(),
            'total_price' => $product->price,
            'status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
        ]);
    }

    public function test_purchased_user_can_post_review_once(): void
    {
        $this->seedItemListDependencies();

        $user = User::factory()->create();
        $product = $this->createAvailableProduct();
        $this->markPurchased($user, $product);

        // 未投稿の購入者はフォームが出る想定（画面の一部文言でざっくり確認）
        $this->actingAs($user, 'users')
            ->get('/show/' . $product->id)
            ->assertStatus(200)
            ->assertSee('レビューを投稿する');

        // 投稿（1回目）: 成功してリダイレクト、DB に保存される
        $this->actingAs($user, 'users')
            ->post('/show/' . $product->id . '/reviews', [
                'rating' => 5,
                'comment' => 'とても良い',
            ])
            ->assertStatus(302)
            ->assertSessionHas('message', 'レビューを投稿しました。');

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => 'とても良い',
        ]);

        // 2回目: 「投稿済み」エラーで弾かれ、レビュー件数が増えない
        $this->actingAs($user, 'users')
            ->from('/show/' . $product->id)
            ->post('/show/' . $product->id . '/reviews', [
                'rating' => 4,
                'comment' => '二回目',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors(['review']);

        $this->assertSame(1, Review::where('product_id', $product->id)->where('user_id', $user->id)->count());
    }

    public function test_not_purchased_user_cannot_post_review(): void
    {
        $this->seedItemListDependencies();

        $user = User::factory()->create();
        $product = $this->createAvailableProduct();

        // 未購入者が投稿しようとするとエラー
        $this->actingAs($user, 'users')
            ->from('/show/' . $product->id)
            ->post('/show/' . $product->id . '/reviews', [
                'rating' => 5,
                'comment' => '買ってないのに投稿',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors(['review']);

        $this->assertDatabaseMissing('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_unlisted_but_purchased_user_can_view_detail_and_post_review(): void
    {
        $this->seedItemListDependencies();

        $product = $this->createUnavailableProduct();

        $this->assertFalse(
            Product::availableItems()
                ->where('products.id', $product->id)
                ->exists(),
            'この商品は availableItems に含まれない前提'
        );

        $user = User::factory()->create();
        $this->markPurchased($user, $product);

        $this->actingAs($user, 'users')
            ->get('/show/' . $product->id)
            ->assertStatus(200)
            ->assertSee('レビューを投稿する');

        $this->actingAs($user, 'users')
            ->post('/show/' . $product->id . '/reviews', [
                'rating' => 4,
                'comment' => '非掲載でも購入者は投稿できる',
            ])
            ->assertStatus(302)
            ->assertSessionHas('message', 'レビューを投稿しました。');

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 4,
            'comment' => '非掲載でも購入者は投稿できる',
        ]);
    }

    public function test_stranger_gets_404_for_unlisted_product_without_purchase(): void
    {
        $this->seedItemListDependencies();

        $product = $this->createUnavailableProduct();

        $this->assertFalse(
            Product::availableItems()
                ->where('products.id', $product->id)
                ->exists()
        );

        $stranger = User::factory()->create();

        $this->actingAs($stranger, 'users')
            ->get('/show/' . $product->id)
            ->assertStatus(404);
    }

    public function test_owner_review_index_shows_only_own_shop_reviews(): void
    {
        $this->seedItemListDependencies();
        $this->seedSecondShop();

        $productShop1 = $this->createAvailableProductForShop(1);
        $productShop2 = $this->createAvailableProductForShop(2);

        $reviewerA = User::factory()->create();
        $reviewerB = User::factory()->create();

        $commentShop1Only = 'COMMENT_UNIQUE_FOR_SHOP1_ONLY_XYZ';
        $commentShop2Only = 'COMMENT_UNIQUE_FOR_SHOP2_ONLY_XYZ';

        Review::create([
            'product_id' => $productShop1->id,
            'user_id' => $reviewerA->id,
            'rating' => 5,
            'comment' => $commentShop1Only,
        ]);

        Review::create([
            'product_id' => $productShop2->id,
            'user_id' => $reviewerB->id,
            'rating' => 4,
            'comment' => $commentShop2Only,
        ]);

        $ownerShop1 = Owner::query()->findOrFail(1);

        $response = $this->actingAs($ownerShop1, 'owners')
            ->get('/owner/reviews');

        $response->assertStatus(200);
        $response->assertSee($commentShop1Only, false);
        $response->assertDontSee($commentShop2Only, false);

        $ownerShop2 = Owner::query()->findOrFail(2);

        $responseB = $this->actingAs($ownerShop2, 'owners')
            ->get('/owner/reviews');

        $responseB->assertStatus(200);
        $responseB->assertSee($commentShop2Only, false);
        $responseB->assertDontSee($commentShop1Only, false);
    }
}

