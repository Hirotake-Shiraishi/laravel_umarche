<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Shop;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

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
        // phpinfo();
        // ログイン中のユーザーIDを取得
        // $ownerId = Auth::id();

        // owner_id が $ownerId と一致するレコードだけを取得する
        $shops = Shop::where('owner_id', Auth::id())->get();

        return view('owner.shops.index', compact('shops'));
    }

    public function edit($id)
    {
        // echo 'edit';
        $shop = Shop::findOrFail($id);
        return view('owner.shops.edit', compact('shop'));
    }

    public function update(Request $request, $id)
    {
        $imageFile = $request->image; //一時保存

        if (!is_null($imageFile) && $imageFile->isValid()) {
            // Storage::putFile('public/shops', $imageFile); //リサイズなしの場合

            $fileName = uniqid(rand() . '_'); //726829743_6990d02b29725
            $extension = $imageFile->extension(); //jpg
            $fileNameToStore = $fileName . '.' . $extension; //726829743_6990d02b29725.jpg

            // dd($fileName, $extension, $fileNameToStore);

            $resizedImage = Image::make($imageFile)->resize(1920, 1080)->encode();

            // dd($imageFile, $resizedImage); //型は違うことがわかる

            Storage::put('public/shops/' . $fileNameToStore, $resizedImage);
        }

        return redirect()->route('owner.shops.index')
            ->with(['message' => '店舗情報を更新しました。', 'status' => 'info']);
    }
}
