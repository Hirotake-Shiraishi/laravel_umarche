<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Owner;
use App\Models\PrimaryCategory;
use App\Models\Shop;
use App\Models\Image;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:owners');

        // クロージャを使ったコントローラミドルウェア
        $this->middleware(function ($request, $next) {

            $id = $request->route()->parameter('product'); //URLパラメータ productのid取得

            if (!is_null($id)) {

                $productsOwnerId = Product::findOrFail($id)->shop->owner->id;

                // セッションのログインIDと相違があったら
                if ($productsOwnerId !== Auth::id()) {
                    abort(404); // 404画面表示
                }
            }
            return $next($request);
        });
    }


    public function index()
    {
        // $products = Owner::findOrFail(Auth::id())->shop->product;

        // リレーション関連データを事前読み込みした　ログインオーナーのOwnerインスタンス
        $owner = Owner::with('shop.product.imageFirst')
            ->findOrFail(Auth::id());

        // ログインオーナーの商品一覧
        $products = $owner->shop->product;

        return view('owner.products.index', compact('products'));
    }


    public function create()
    {
        $shops = Shop::where('owner_id', Auth::id())
            ->select('id', 'name')
            ->get();

        $images = Image::where('owner_id', Auth::id())
            ->select('id', 'title', 'filename')
            ->orderBy('updated_at', 'desc')
            ->get();

        // リレーション先の情報は、N+1問題を考慮して、withを使用する。
        // 動的プロパティ（モデルに定義のメソッド名）を渡す。
        $categories = PrimaryCategory::with('secondary')
            ->get();

        return view('owner.products.create', compact('shops', 'images', 'categories'));
    }


    public function store(Request $request)
    {
        //
    }


    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        //
    }
}
