<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Stock;
use App\Services\CartService;
use App\Jobs\SendThanksMail;
use App\Jobs\SendOrderedMail;
use App\Http\Requests\CartAddRequest; //add()のバリデーション用

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
     * 【指摘#2】line_items: 二重配列 [$lineItems] → $lineItems。price_data + product_data 形式（Stripe v7 以降）。
     * 【指摘#3】Stripe 失敗時は在庫を戻す（課題2でトランザクションとStripeを分離させるため、Stripe失敗時に明示的に在庫戻しを行う必要がある）。
     * 【指摘#9】Apikey を env()ではなく、config('services.stripe.secret'|'public') で取得。（config:cache 後も動作させるため）
     * 【課題1】在庫の取得・チェック・減算を同一トランザクション内で lockForUpdate() により悲観的ロック。
     * 【課題2】トランザクション内に外部API（Stripe）を含めない。在庫減算のみトランザクションで行い、コミット後にStripe呼び出し。Stripe失敗時は在庫を戻す。
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
     * Stripe 決済完了後のコールバック
     * メール送信（ThanksMail / OrderedMail）を dispatch し、
     * カートを削除して商品一覧へリダイレクトする。
     */
    public function success()
    {
        ////
        $items = Cart::where('user_id', Auth::id())->get(); //where句は最後にget()が必要

        $products = CartService::getItemsInCart($items);

        $user = User::findOrfail(Auth::id());

        // 非同期でメールを送信
        // jobクラスに、引数として「商品情報」と「ユーザー情報」を渡す

        // ユーザーに感謝メールを送信
        SendThanksMail::dispatch($products, $user);

        // オーナーに注文メールを送信
        // 商品により、オーナーが異なるため、foreachでループ処理する。
        // Mailtrap等のレート制限（1秒あたりのメール数）を避けるため、各ジョブに遅延を付与
        foreach($products as $index => $product){ // インデックス番号も取得
            SendOrderedMail::dispatch($product, $user)
                ->delay(now()->addSeconds(5 + $index * 5));
        }

        // dd('ユーザーサンクスメール送信テスト');
        ////

        // カート内の商品を削除
        Cart::where('user_id', Auth::id())->delete();

        // 成功時は、商品一覧ページにリダイレクト
        return redirect()->route('user.items.index');
    }


    /**
     * Stripe チェックアウト画面でユーザーがキャンセルしたときのコールバック
     *
     * 【課題2】checkout() で在庫を事前減算しているため、キャンセル時は在庫を戻す。
     * （Webhook 方式の場合は checkout() で減算しないため不要になる）
     */
    public function cancel()
    {
        $user = User::findOrfail(Auth::id());

        // キャンセル時はカート内の商品を在庫に戻す
        foreach($user->products as $product){
            Stock::create([
                'product_id' => $product->id,
                'type' => \Constant::PRODUCT_LIST['add'],
                'quantity' => $product->pivot->quantity
            ]);
        }

        // キャンセル時は、カートページにリダイレクト
        return redirect()->route('user.cart.index');
    }
}
