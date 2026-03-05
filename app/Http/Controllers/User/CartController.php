<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Stock;

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

    public function add(Request $request)
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
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        // \Stripe\Checkout\Session::createでセッションを作成
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'], // カードのみに制限する場合
            'line_items' => $lineItems, // 商品情報
            'mode' => 'payment', // 決済モード 1回限りの決済
            'success_url' => route('user.items.index'), // 成功時のリダイレクトURL
            'cancel_url' => route('user.cart.index'), // キャンセル時のリダイレクトURL
        ]);

        $publicKey = env('STRIPE_PUBLIC_KEY');

        return view('user.checkout', compact('session', 'publicKey'));
    }
}
