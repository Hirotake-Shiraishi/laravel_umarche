<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Shop;

class ShopController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:owners');

        // クロージャを使ったコントローラミドルウェア
        $this->middleware(function ($request, $next) {

            // dd($request->route()->parameter('shop'));

            $id = $request->route()->parameter('shop'); //URLパラメータ shopのid取得

            if (!is_null($id)) { //null判定

                $shopsOwnerId = Shop::findOrFail($id)->owner_id;

                $ownerId = Auth::id();

                if ($shopsOwnerId !== $ownerId) { // 同じでなかったら
                    abort(404); // 404画面表示
                }
            }
            return $next($request);
        });
    }

    public function index()
    {
        // ログイン中のユーザーIDを取得
        // $ownerId = Auth::id();

        // owner_id が $ownerId と一致するレコードだけを取得する
        $shops = Shop::where('owner_id', Auth::id())->get();

        return view('owner.shops.index', compact('shops'));
    }

    public function edit($id) {
        echo 'edit';
    }

    public function update(Request $request, $id) {}
}
