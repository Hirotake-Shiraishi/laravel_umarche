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
use Illuminate\Support\Facades\DB;
use Throwable;
use Illuminate\Support\Facades\Log;
use App\Models\Stock;
use App\Http\Requests\ProductRequest;

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


    public function store(ProductRequest $request)
    {
        try {
            // トランザクションは、引数で無名関数(クロージャー)を受け取る。
            // フォームで入力されて渡ってきた値 $request をクロージャーに渡すには、
            // use($request) を記載することで、クロージャー内で、$request 使用可能となる。
            DB::transaction(function () use ($request) {

                $product = Product::create([
                    'name' => $request->name,
                    'information' => $request->information,
                    'price' => $request->price,
                    'sort_order' => $request->sort_order,
                    'shop_id' => $request->shop_id,
                    'secondary_category_id' => $request->category,
                    'image1' => $request->image1,
                    'image2' => $request->image2,
                    'image3' => $request->image3,
                    'image4' => $request->image4,
                    'is_selling' => $request->is_selling,
                ]);


                Stock::create([
                    'product_id' => $product->id,
                    'type' => 1,
                    'quantity' => $request->quantity,
                ]);

                // 第二引数:トランザクションを再試行する回数
            }, 2);
        } catch (Throwable $e) {
            Log::error($e);
            throw $e;
        }

        return redirect()
            ->route('owner.products.index')
            ->with(['message' => '商品を登録しました', 'status' => 'info']);
    }


    public function edit($id)
    {
        $product = Product::findOrFail($id);

        $quantity = Stock::where('product_id', $product->id)
            ->sum('quantity');

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

        return view('owner.products.edit', compact('product', 'quantity', 'shops', 'images', 'categories'));
    }


    public function update(ProductRequest $request, $id)
    {
        // update のときだけのバリデーション
        $request->validate([
            'current_quantity' => 'required|integer',
        ]);

        $product = Product::findOrFail($id);

        $quantity = Stock::where('product_id', $product->id)
            ->sum('quantity');

        if ($request->current_quantity !== strval($quantity)) {
            $id = $request->route()->parameter('product');
            return redirect()->route('owner.products.edit', ['product' => $id])
                ->with(['message' => '在庫数が変更されています。再度確認してください。', 'status' => 'alert']);
        } else {
            try {
                // トランザクションは、引数で無名関数(クロージャー)を受け取る。
                // フォームで入力されて渡ってきた値 $request をクロージャーに渡すには、
                // use($request) を記載することで、クロージャー内で、$request 使用可能となる。
                DB::transaction(function () use ($request, $product) {

                    $product->name = $request->name;
                    $product->information = $request->information;
                    $product->price = $request->price;
                    $product->sort_order = $request->sort_order;
                    $product->shop_id = $request->shop_id;
                    $product->secondary_category_id = $request->category;
                    $product->image1 = $request->image1;
                    $product->image2 = $request->image2;
                    $product->image3 = $request->image3;
                    $product->image4 = $request->image4;
                    $product->is_selling = $request->is_selling;

                    // ::create ではないので、saveとしてDBに保存する必要がある。
                    $product->save();

                    // 在庫数追加・削減の判定
                    if ($request->type === \Constant::PRODUCT_LIST['add']) {
                        $newQuantity = $request->quantity;
                    }
                    if ($request->type === \Constant::PRODUCT_LIST['reduce']) {
                        $newQuantity = $request->quantity * -1;
                    }

                    // stockは、毎回新規作成。
                    Stock::create([
                        'product_id' => $product->id,
                        'type' => $request->type,
                        'quantity' => $newQuantity,
                    ]);

                    // 第二引数:トランザクションを再試行する回数
                }, 2);
            } catch (Throwable $e) {
                Log::error($e);
                throw $e;
            }

            return redirect()
                ->route('owner.products.index')
                ->with(['message' => '商品情報を更新しました', 'status' => 'info']);
        }
    }


    public function destroy($id)
    {
        // DB Imagesテーブルのレコードを削除
        product::findOrFail($id)->delete();

        return redirect()->route('owner.products.index')
            ->with(['message' => '商品を削除しました。', 'status' => 'alert']);
    }
}
