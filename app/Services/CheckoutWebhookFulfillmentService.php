<?php

namespace App\Services;

use App\Exceptions\CheckoutStockInsufficientException;
use App\Jobs\SendThanksMail;
use App\Jobs\SendOrderedMail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as CheckoutSession;

/**
 * Stripe Checkout 完了（Webhook）後に行う「注文の確定処理」をまとめたサービスクラス
 *
 * 【なぜコントローラではなく Service に書くのか】
 * - 処理が長くなる（在庫・注文・メール・カート削除・返金など）ので、コントローラを薄く保つ。
 * - 将来、テストや別の入口から同じ処理を呼びたいときに再利用しやすい。
 *
 * 【このクラスが担う責務】
 * 1. 同じ Stripe セッションで二重に処理しない（冪等性）
 * 2. トランザクション内で在庫を lockForUpdate し、競合時も在庫マイナスを防ぐ
 * 3. メールジョブを dispatch、カートを空にする
 * 4. 在庫不足やカート空など異常時は、可能なら Stripe 返金 API を試す
 *
 * 【Webhook にはログインセッションが無い理由】
 * - Stripe のサーバーからサーバーへの POST なので、ブラウザの Cookie（ログイン状態）は付かない。
 * - そのため checkout 時に Session に付けた client_reference_id（ユーザー ID）で「誰のカートか」を特定する。
 */
class CheckoutWebhookFulfillmentService
{
    /**
     * checkout.session.completed で渡された Checkout Session をもとに、購入後処理を行う
     *
     * @param  CheckoutSession  $session  Stripe から送られたセッションオブジェクト（id, client_reference_id など）
     * @return void  正常・スキップ・返金試行いずれも戻り値なし（ログや返金で異常は吸収）
     */
    public function fulfill(CheckoutSession $session): void
    {
        $stripeSessionId = $session->id;

        // checkout() で Session::create 時に設定した「どのユーザーか」の目印。
        // 数字の文字列だけを許可し、それ以外は不正データとして処理しない（課金の不正利用防止の基本）。
        $ref = $session->client_reference_id;

        // client_reference_id のバリデーション
        // Stripe 側に保存された値は改ざんされうるため、「本当にユーザーIDらしい形」か確認する。
        //
        // ctype_digit((string) $ref) … PHP組み込み関数。引数を文字列にしたうえで「先頭から末尾までが 0〜9 の数字だけか」を調べる。
        //    - 真なら "123" のような「非負の整数の十進表記」だけ。偽なら "12a"、"-1"、"1.5"、半角スペース付きなどすべて弾く。
        //    - 先頭の 0 も数字なので "007" は真（この後 (int) で 7 になる）。負の ID を使わない前提ならこのチェックで足りる。
        //    - (string) は、Stripe SDK が数値型で返す場合にも ctype_digit が文字列前提で動くようにするための型そろえ。
        // !ctype_digit(...) … 「数字だけではない」＝不正なので、警告ログを残して以降の処理へ進まない。
        if ($ref === null || $ref === '' || !ctype_digit((string) $ref)) {

            // Log::warning(メッセージ, 連想配列) … 第2引数はログに残す追加情報（検索・調査しやすくするため）。
            Log::warning('Stripe webhook: invalid client_reference_id', ['session' => $stripeSessionId]);

            return;
        }

        // ここまで来た $ref は "123" のような数字だけの文字列なので、(int) で整数のユーザーID に変換する。
        $userId = (int) $ref;

        // User::whereKey($userId) … 主キー（通常 id）が $userId の行を探すクエリを組み立てる。
        // ->exists() … 1件でもあれば true。存在しなければ false（ここでは ! で「いなければ警告して終了」）。
        if (!User::whereKey($userId)->exists()) {
            Log::warning('Stripe webhook: user not found', ['user_id' => $userId]);

            return;
        }


        // 在庫不足時に全額返金するために PaymentIntent の ID が必要（Checkout の mode=payment では通常セットされる）。
        $paymentIntentId = $this->resolvePaymentIntentId($session);

        try {
            //「全部成功 or 全部取り消し（ロールバック）」にしたいのでトランザクションで囲む。
            $result = DB::transaction(function () use ($stripeSessionId, $userId) {

                // 冪等性: 同じ stripe_session_id の注文が既にあれば何もしない。
                // lockForUpdate() で行ロックし、同時に2つの Webhook が来ても片方が待ってから判定できる。
                if (Order::where('stripe_session_id', $stripeSessionId)->lockForUpdate()->first()) {
                    return ['status' => 'duplicate'];
                }

                $items = Cart::where('user_id', $userId)->get();
                if ($items->isEmpty()) {
                    // 決済は済んだのにカートが空 -> レアなケースだが、返金候補としてマークする。
                    return ['status' => 'empty_cart'];
                }

                // 同一商品が複数カート行に分かれている可能性に備え、商品ID ごとに必要数量を合算する。
                $quantitiesByProduct = [];
                foreach ($items as $item) {
                    $pid = (int) $item->product_id;
                    $quantitiesByProduct[$pid] = ($quantitiesByProduct[$pid] ?? 0) + (int) $item->quantity;
                }

                // 在庫確認
                foreach ($quantitiesByProduct as $productId => $qtyNeeded) {

                    // 在庫数を取得
                    // product_id に紐づく stocks の行を FOR UPDATE で悲観ロック
                    // - 他トランザクションが同じ行を更新するのを待たせる／競合を抑える）
                    $available = (int) Stock::where('product_id', $productId)
                        ->lockForUpdate()
                        ->sum('quantity');

                    // 必要数 > 在庫数 -> 在庫不足
                    if ($qtyNeeded > $available) {

                        // ここで投げた例外はトランザクション外で catch し、返金フローへ。
                        throw new CheckoutStockInsufficientException('在庫不足');
                    }
                }

                // 注文ヘッダの total_price はカート画面と同じ考え方（明細の単価×数量の合計）。
                $totalPrice = 0;
                foreach ($items as $item) {
                    $product = Product::findOrFail($item->product_id);
                    $totalPrice += $product->price * $item->quantity;
                }

                // 在庫履歴テーブルに「減算」行を追加する。
                foreach ($quantitiesByProduct as $productId => $qtyNeeded) {
                    Stock::create([
                        'product_id' => $productId,
                        'type' => \Constant::PRODUCT_LIST['reduce'],
                        'quantity' => $qtyNeeded * -1,
                    ]);
                }

                // 注文ヘッダを作成
                $order = Order::create([
                    'user_id' => $userId,
                    'stripe_session_id' => $stripeSessionId,
                    'total_price' => $totalPrice,
                    'status' => \Constant::ORDER_STATUS_PENDING,
                ]);

                // 注文明細を作成
                //  - 明細の price は「購入時点の単価」のコピー。
                //  - あとから商品マスタの価格が変わっても履歴は変わらない。
                foreach ($items as $item) {
                    $product = Product::findOrFail($item->product_id);
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $item->quantity,
                        'price' => $product->price,
                    ]);
                }

                // メール用の配列は CartService の getItemsInCart メソッドで取得する。
                // 取得だけおこない、カート内の削除はトランザクション成功・コミット後に行う。
                $productsForMail = CartService::getItemsInCart($items);

                // $result には、以下の配列を格納して返す。
                return [
                    'status' => 'fulfilled',
                    'productsForMail' => $productsForMail,
                    'userId' => $userId,
                ];
            });

        } catch (QueryException $e) {

            // 例外が投げられると、トランザクションはロールバックされる。

            // Webhook がほぼ同時リクエストになり、両方の注文が INSERT まで進んでしまった場合、
            // stripe_session_id は UNIQUE(重複キー) なので、
            // 先に入った方は成功し、後から来た方は DB が「重複」と判断して QueryException を投げる。

            // 「重複キー」エラーの場合(true)は、一方は成功しているので、エラーを無視して静かに終了する。
            if ($this->isDuplicateStripeSessionError($e)) {
                return;
            }

            // 「重複キー」エラーではない場合(false)は、エラーを再スローする。
            throw $e;

        } catch (CheckoutStockInsufficientException $e) {

            // お客様は決済済みなので、返金を試みる。
            $this->refundPaymentIntent($paymentIntentId);

            return;
        }


        // $result が配列でないときは、ここで fulfillメソッドを終了させる。
        //  - 通常はクロージャが必ず ['status' => ...] 形式の配列を返すはずだが、想定外の戻り値のときのガード。
        //  - これ以降の処理で、配列でないのに $result['status'] などに触るとエラーになるので、その前に止める。
        if (!is_array($result)) {
            return;
        }

        // 重複キーエラーの場合は、何もしない。
        if (($result['status'] ?? '') === 'duplicate') {
            return;
        }

        // カートが空の場合は、返金を試みる。
        // $result['status'] が存在しない（null/undefined など）場合 ''（空文字）を使う（?? は「null合体演算子」）
        if (($result['status'] ?? '') === 'empty_cart') {

            $this->refundPaymentIntent($paymentIntentId);
            Log::warning('Stripe webhook: cart empty after payment', ['session' => $stripeSessionId, 'user_id' => $userId]);

            return;
        }

        // 決済が成功していない場合は、ここで fulfillメソッドを終了させる。
        if (($result['status'] ?? '') !== 'fulfilled') {
            return;
        }


        // トランザクションがコミットされたあとでメール送信（失敗しても注文データは残す方針）。
        $user = User::findOrFail($result['userId']);
        $productsForMail = $result['productsForMail'];

        // 購入者へのサンクスメールを送信
        SendThanksMail::dispatch($productsForMail, $user);

        // 各商品のオーナーへの注文通知メールを送信
        foreach ($productsForMail as $index => $product) {
            SendOrderedMail::dispatch($product, $user)
                ->delay(now()->addSeconds(5 + $index * 5));
        }

        // 最後にカートを空にする。
        Cart::where('user_id', $userId)->delete();
    }


    /**
     * Checkout Session から PaymentIntent ID を取り出す（文字列 or オブジェクトの両方に対応）
     *
     * @param  CheckoutSession  $session
     * @return string|null  取得できなければ null（返金 API が呼べない）
     */
    private function resolvePaymentIntentId(CheckoutSession $session): ?string
    {
        $pi = $session->payment_intent;
        if (is_string($pi) && $pi !== '') {
            return $pi;
        }
        if (is_object($pi) && isset($pi->id)) {
            return $pi->id;
        }

        return null;
    }


    /**
     * Stripe に全額返金を依頼する（API キーが無い・失敗時はログのみ）
     *
     * @param  string|null  $paymentIntentId
     * @return void
     */
    private function refundPaymentIntent(?string $paymentIntentId): void
    {
        if ($paymentIntentId === null || $paymentIntentId === '') {
            return;
        }

        $secret = config('services.stripe.secret');
        if ($secret === null || $secret === '') {
            return;
        }

        try {
            \Stripe\Stripe::setApiKey($secret);
            \Stripe\Refund::create(['payment_intent' => $paymentIntentId]);
        } catch (\Throwable $e) {
            Log::error('Stripe refund failed', [
                'payment_intent' => $paymentIntentId,
                'message' => $e->getMessage(),
            ]);
        }
    }


    /**
     * MySQL 等の「重複キー」エラーかどうかをざっくり判定（stripe_session_id UNIQUE 用）
     *
     * @param  QueryException  $e
     * @return bool
     */
    private function isDuplicateStripeSessionError(QueryException $e): bool
    {
        if ($e->getCode() === '23000') {
            return true;
        }

        return strpos($e->getMessage(), 'Duplicate') !== false;
    }
}
