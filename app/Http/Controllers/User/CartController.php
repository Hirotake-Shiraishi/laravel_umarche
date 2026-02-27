<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

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

        dd($products, $totalPrice);

        return view('user.cart.index', compact('totalPrice'));
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
}
