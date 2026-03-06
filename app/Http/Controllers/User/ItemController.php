<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Models\Stock;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:users');

        // クロージャを使ったコントローラミドルウェア
        $this->middleware(function ($request, $next) {

            $id = $request->route()->parameter('item'); //ルートパラメータのid {item}

            if (!is_null($id)) {

                // 表示可能な商品（在庫が1以上などの条件を満たす）の中で、指定された商品IDが存在するかどうか
                $itemId = Product::availableItems()->where('products.id', $id)->exists();

                // 指定された商品IDが存在しない場合は、404画面を表示
                if (!$itemId) {
                    abort(404);
                }
            }
            return $next($request);
        });
    }

    // ビューからのリクエストを受け取るために、Request $requestを引数に追加
    public function index(Request $request)
    {
        // ローカルスコープに、クエリを定義
        $products = Product::availableItems() // 表示可能な商品
            ->sortOrder($request->sort) // 並び順
            ->paginate($request->pagination); // ページネーション

        return view('user.index', compact('products'));
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);

        $quantity = Stock::where('product_id', $product->id)
            ->sum('quantity');

        // 在庫が10以上あれば、数量は9まで表示する
        if($quantity > 9){
            $quantity = 9;
        }

        return view('user.show', compact('product', 'quantity'));
    }
}
