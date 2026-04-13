<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Requests\CartAddRequest; //add()のバリデーション用

/**
 * 一般ユーザーのカート・Stripe Checkout 関連
 *
 * - checkout / success / cancel … ブラウザ起点の画面遷移（Session 作成・案内・キャンセル戻り）
 * - 在庫・注文の確定 … StripeWebhookController → CheckoutWebhookFulfillmentService（別ファイル）
 */
class CartController extends Controller
{
    public function index()
    {
        $user = User::findOrfail(Auth::id());
        $products = $user->products;
        $totalPrice = 0;

        foreach($products as $product){
            // 値段 * 数量
            $totalPrice += $product->price * $product->pivot->quantity;
        }

        // dd($products, $totalPrice);

        return view('user.cart', compact('products', 'totalPrice'));
    }


    /**
     * カートに商品を追加する
     *
     * - 既に同一商品がカートに存在する場合は数量を加算する
     * - 存在しない場合は新規作成する
     *
     * @param \App\Http\Requests\CartAddRequest $request バリデーション済みリクエスト
     * @return \Illuminate\Http\RedirectResponse カート一覧画面へのリダイレクト
     */
    public function add(CartAddRequest $request)
    {
        // ログインユーザーIDで絞る → リクエストで送られてきた商品IDで絞る → 商品があれば取得、なければnull
        $itemInCart = Cart::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        if($itemInCart){
            // あれば数量を加算
            $itemInCart->quantity += $request->quantity;
            $itemInCart->save();
        } else {
            // なければ新規作成
            Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity
            ]);
        }

        return redirect()->route('user.cart.index');
    }


    public function delete($id)
    {
        Cart::where('product_id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        return redirect()->route('user.cart.index');
    }


    /**
     * チェックアウト
     *
     * ログインユーザーのカート内容から Stripe 用の line_items を組み立てる。
     * - Stripe API で Checkout Session を作成し、決済画面へ誘導するための情報をビューに渡す。
     * - client_reference_id にユーザー ID を入れる … Webhook には Cookie が無いので、「誰の購入か」をこれで伝える。
     */
    public function checkout()
    {
        $user = User::findOrfail(Auth::id());
        $products = $user->products;

        $lineItems = [];
        foreach($products as $product){

            // Stripe Checkout に渡す行データ。
            // price_data + product_data 形式で渡す（Stripev7以降で必須）
            $lineItem = [
                'price_data' => [
                    'currency' => 'jpy',
                    'unit_amount' => $product->price,
                    'product_data' => [
                        'name' => $product->name,
                        'description' => $product->information,
                    ],
                ],
                'quantity' => $product->pivot->quantity,
            ];
            // $lineItems配列に$lineItemを追加
            array_push($lineItems, $lineItem);

            // ※上記のコードの書き換え。同じ意味であるが、より読みやすいコードになっている。
            // $lineItems[] = [
            //     'price_data' => [
            //         'currency' => 'jpy',
            //         'unit_amount' => $product->price,
            //         'product_data' => [
            //             'name' => $product->name,
            //             'description' => $product->information,
            //         ],
            //     ],
            //     'quantity' => $product->pivot->quantity,
            // ];
        }
        // dd($lineItems);

        // Stripe の公開鍵の取得。これをクライアントサイドで Stripe.js に渡して、決済ページを表示する。
        $publicKey = config('services.stripe.public');

        try {
            // setApiKeyで秘密鍵を、Stripe PHP SDKのAPIキーに設定
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            // Checkout\Session::create で Stripe が作成する checkout session に必要なパラメータを設定する。
            $session = \Stripe\Checkout\Session::create([

                // カードのみに制限する場合
                'payment_method_types' => ['card'],

                // Stripe Checkout に渡す行データ。
                'line_items' => $lineItems,

                // 決済モード 1回限りの決済
                'mode' => 'payment',

                // Webhook で「この決済はユーザー ID ○○ のもの」と突き合わせるための任意文字列（公式もこの用途を想定）。
                'client_reference_id' => (string) $user->id,

                // 決済成功後にブラウザが戻るURL。{CHECKOUT_SESSION_ID} は Stripe側で実セッションIDに置換する。
                'success_url' => route('user.cart.success', [], true) . '?session_id={CHECKOUT_SESSION_ID}',

                // ユーザーが Stripe決済画面で「戻る」を押したときのURL。
                'cancel_url' => route('user.cart.cancel', [], true),
            ]);

        } catch (\Throwable $e) {

            // APIキー不正エラー・ネットワークエラー・Stripe側エラー等のエラー処理
            return redirect()->route('user.cart.index')
                ->with(['message' => '決済の開始に失敗しました。', 'status' => 'alert']);
        }

        // checkout.blade で Stripe.redirectToCheckout({ sessionId }) を実行する。
        return view('user.checkout', compact('session', 'publicKey'));
    }


    /**
     * Stripe 決済完了後の success_url コールバック（副作用なし）
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function success()
    {
        // 商品一覧ページにリダイレクト
        return redirect()->route('user.items.index')
            ->with(['message' => 'ご購入ありがとうございました。', 'status' => 'info']);;
    }


    /**
     * Stripe チェックアウト画面でユーザーがキャンセルしたときのコールバック（cancel_url）
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel()
    {
        // キャンセル時は、カートページにリダイレクト
        return redirect()->route('user.cart.index');
    }
}
