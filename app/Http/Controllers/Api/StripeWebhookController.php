<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CheckoutWebhookFulfillmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\Webhook;

/**
 * Stripe から送られてくる Webhook（HTTP POST）を受け取るコントローラ
 *
 * 【このコントローラの目的】
 * - 「本当に Stripe から来た通知か」を署名で検証し、偽装リクエストを拒否する。
 * - 決済完了イベント（checkout.session.completed）のときだけ、在庫・注文・メールなどの本処理をおこなう。
 *
 * 【なぜブラウザの success URL ではなく Webhook なのか】
 * - ユーザーのブラウザは閉じたり、success に来る前に通信が切れたりする。
 *   その場合でも Stripe 側は「支払い完了」なので、サーバー同士の Webhook で確実に後処理するのが定石。
 * - 在庫減算を「決済が確定したあと」にだけ行えるため、決済と在庫の不整合を減らせる。
 *
 * 【__invoke とは】
 * - Laravel では「このクラス全体が1アクションのコントローラ」として使える。
 * - routes/api.php で StripeWebhookController::class と書くと、この __invoke が実行される。
 */
class StripeWebhookController extends Controller
{
    /**
     * Stripe からの POST を処理するエントリポイント
     *
     * @param  Request  $request  生のHTTPボディとStripe-Signatureヘッダを含むリクエスト
     * @param  CheckoutWebhookFulfillmentService  $fulfillment  注文確定・在庫更新などの業務ロジック
     * @return \Illuminate\Http\Response  本文は空でもよい。Stripe は主に HTTP ステータスで成否を判断する。
     * @throws \Stripe\Exception\SignatureVerificationException 署名が一致しない場合
     * @throws \Stripe\Exception\UnexpectedValueException JSON が壊れている場合
     */
    public function __invoke(Request $request, CheckoutWebhookFulfillmentService $fulfillment)
    {
        // Stripe の署名検証は「改ざんされていない生の JSON」と「ヘッダの署名」を組み合わせて行う。
        // $request->all() や JSON を一度配列にしてから json_encode し直すと、
        // キー順などが変わり、署名が合わなくなることがあるため、必ず getContent() で生文字列を使う。
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        // .env に STRIPE_WEBHOOK_SECRET が無いと署名検証不能。設定ミスなので 500 とし、ログに残す。
        if ($secret === null || $secret === '') {
            Log::error('Stripe webhook secret is not configured');

            return response('Webhook secret not configured', 500);
        }

        // 署名ヘッダが無いリクエストは Stripe 由来ではない可能性が高い → 400 Bad Request
        if ($sigHeader === null || $sigHeader === '') {
            return response('Missing Stripe-Signature', 400);
        }

        try {
            // Stripe公式ライブラリ（Stripe PHP SDK）の Webhook::constructEvent（セキュリティ検証メソッド）
            // タイムスタンプ・署名を検証し、問題なければ Event オブジェクトに変換する。
            // 失敗時は例外になるので catch で 400 を返す（課題要件: 不正は 400）。
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);

        } catch (UnexpectedValueException $e) {
            // JSON が壊れているなど
            return response('Invalid payload', 400);

        } catch (SignatureVerificationException $e) {
            // 署名が一致しない（別シークレットで署名された、改ざんされた、など）
            return response('Invalid signature', 400);
        }

        // 興味があるのは「Checkout が完了した」イベントだけ。他イベントは 200 で無視してよい（再送を止めるため）。
        if ($event->type === 'checkout.session.completed') {

            try {
                // $event->data->object には Checkout Session の情報が入る（支払い済みのセッション）。
                $fulfillment->fulfill($event->data->object);

            } catch (\Throwable $e) {
                // DB 障害など想定外の例外。500 を返すと Stripe は一定回数リトライしてくれる場合がある。
                Log::error('Stripe webhook fulfillment failed', [
                    'message' => $e->getMessage(),
                    'exception' => $e,
                ]);

                return response('Fulfillment error', 500);
            }
        }

        // Stripe には「受け取ったよ」と伝える。本文空でも 200 なら成功扱い。
        return response('', 200);
    }
}
