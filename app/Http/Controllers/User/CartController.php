<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
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
     * カートに商品を追加
     * 【指摘#5 修正】quantity・product_id にバリデーションがなく、負の数・存在しないIDで孤立レコードが作れるようになってしまっていた。
     * CartAddRequestを作成し、バリデーションを実施。
     */
    public function add(CartAddRequest $request)
    {
        // dd($request);

        // カートに商品があるか
        // ユーザーIDが、ログインIDと同じか
        $itemInCart = Cart::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)->first();

        if($itemInCart){
            // あれば数量を追加
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

        // dd('テスト');
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
     * 【指摘#9】Apikey を env()ではなく、config('services.stripe.secret'|'public') で取得。（config:cache 後も動作させるため）
     */
    public function checkout()
    {
        $user = User::findOrfail(Auth::id());
        $products = $user->products;

        $lineItems = [];
        foreach($products as $product){

            // Stockテーブルから、商品IDに紐づく在庫数を取得
            $quantity = '';
            $quantity = Stock::where('product_id', $product->id)->sum('quantity');

            // カート内の数量が、在庫数より多い場合は、カートにリダイレクトする。
            if($product->pivot->quantity > $quantity){
                return redirect()->route('user.cart.index');

            } else {
                // 指摘#2 修正: price_data 形式で渡す（Stripev7以降で必須）
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
            }
        }
        // dd($lineItems);

        // Stripeで決済する前に、在庫数を減らす。
        foreach($products as $product){
            Stock::create([
                'product_id' => $product->id,
                'type' => \Constant::PRODUCT_LIST['reduce'],
                'quantity' => $product->pivot->quantity * -1
            ]);
        }

        // dd('Stripe決済前　在庫減算処理テスト');

        // setApiKeyで秘密鍵を、Stripe PHP SDKのAPIキーに設定
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        // \Stripe\Checkout\Session::createでセッションを作成
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'], // カードのみに制限する場合
            'line_items' => $lineItems, // 商品情報
            'mode' => 'payment', // 決済モード 1回限りの決済
            'success_url' => route('user.cart.success'), // 成功時のリダイレクトURL
            'cancel_url' => route('user.cart.cancel'), // キャンセル時のリダイレクトURL
        ]);

        $publicKey = config('services.stripe.public');

        return view('user.checkout', compact('session', 'publicKey'));
    }


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
