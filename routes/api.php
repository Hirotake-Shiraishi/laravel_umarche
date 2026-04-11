<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StripeWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/*
 * Stripe Webhook（サーバー間通信）
 *
 * - RouteServiceProviderで api というプレフィックスをつけているため、実際のURLは /api/webhook/stripe になる。
 *
 * - web ミドルウェアではないので、通常ブラウザ向けの CSRF トークンは不要（Stripe が署名で改ざんを防ぐ）。
 * - 誰でも URL を叩めるので、StripeWebhookController 内で Stripe-Signature を必ず検証すること。
 */
Route::post('/webhook/stripe', StripeWebhookController::class);
