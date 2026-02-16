<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Shop;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Http\Requests\UploadImageRequest;
use App\Services\ImageService;

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

    public function update(UploadImageRequest $request, $id)
    {
        // 属性のバリデーション
        $request->validate([
            'name' => 'required|string|max:50',
            'information' => 'required|string|max:1000',
            'is_selling' => 'required',
        ]);

        // 画像のアップロード・保存
        $imageFile = $request->image; //一時保存
        if (!is_null($imageFile) && $imageFile->isValid()) {
            $fileNameToStore = ImageService::upload($imageFile, 'shops');
        }

        // DBへの保存
        $shops = Shop::findOrFail($id);
        $shops->name = $request->name;
        $shops->information = $request->information;
        $shops->is_selling = $request->is_selling;
        if (!is_null($imageFile) && $imageFile->isValid()) {
            $shops->filename = $fileNameToStore; //ファイル名の保存
        }
        $shops->save();

        return redirect()->route('owner.shops.index')
            ->with(['message' => '店舗情報を更新しました。', 'status' => 'info']);
    }
}
