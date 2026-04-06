<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UploadImageRequest;
use App\Services\ImageService;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:owners');

        // クロージャを使ったコントローラミドルウェア
        $this->middleware(function ($request, $next) {

            $id = $request->route()->parameter('image'); //URLパラメータ imageのid取得

            if (!is_null($id)) {

                $imagesOwnerId = Image::findOrFail($id)->owner_id;

                // セッションのログインIDと相違があったら
                if ($imagesOwnerId !== Auth::id()) {
                    abort(404); // 404画面表示
                }
            }
            return $next($request);
        });
    }


    public function index()
    {
        // owner_id ・セッションのログインIDが一致するレコードだけを取得する
        $images = Image::where('owner_id', Auth::id())
            ->orderBy('updated_at', 'desc') //降順に並び替え（更新頻度が高い順）
            ->paginate(20); //paginateの時は、get不要

        return view('owner.images.index', compact('images'));
    }


    public function create()
    {
        return view('owner.images.create');
    }


    public function store(UploadImageRequest $request)
    {
        $imageFiles = $request->file('files');

        if (!is_null($imageFiles)) {

            foreach ($imageFiles as $imageFile) {
                $fileNameToStore = ImageService::upload($imageFile, 'products');

                Image::create([
                    'owner_id' => Auth::id(),
                    'filename' => $fileNameToStore,
                ]);
            }
        }

        return redirect()->route('owner.images.index')
            ->with(['message' => '画像登録が完了しました。', 'status' => 'info']);
    }


    public function edit($id)
    {
        $image = Image::findOrFail($id);
        return view('owner.images.edit', compact('image'));
    }


    public function update(Request $request, $id)
    {
        // 属性のバリデーション
        $request->validate([
            'title' => 'string|max:50',
        ]);

        // DBへの保存
        $image = Image::findOrFail($id);
        $image->title = $request->title;

        $image->save();

        return redirect()->route('owner.images.index')
            ->with(['message' => '画像情報を更新しました。', 'status' => 'info']);
    }


    /**
     * 画像削除
     * （指摘#11）
     * 【問題点①】Collection の空判定: if($imageInProducts) は空コレクションでも true になる。
     * isNotEmpty() で正しく空判定するように修正。
     * 【問題点②】末尾で Image::findOrFail($id)->delete() は二重クエリになる。
     * 取得済みの $image を使うように修正。
     */
    public function destroy($id)
    {
        $image = Image::findOrFail($id);

        // 削除する画像をProductで使っているかの確認
        $imageInProducts = Product::where('image1', $image->id)
            ->orWhere('image2', $image->id)
            ->orWhere('image3', $image->id)
            ->orWhere('image4', $image->id)
            ->get();

        // 修正: if($imageInProducts) → isNotEmpty() で空コレクションを正しく判定
        if($imageInProducts->isNotEmpty()){
            // eachメソッドを使って、１つずつ要素に処理をかける。
            // コールバック関数の中で、$image を使いたいので、useで $image を渡す。
            $imageInProducts->each(function($product) use($image){

                if($product->image1 === $image->id){
                    $product->image1 = null;
                    $product->save();
                }
                if($product->image2 === $image->id){
                    $product->image2 = null;
                    $product->save();
                }
                if($product->image3 === $image->id){
                    $product->image3 = null;
                    $product->save();
                }
                if($product->image4 === $image->id){
                    $product->image4 = null;
                    $product->save();
                }
            });
        }

        $filePath = 'public/products/' . $image->filename;

        // storageの画像ファイルを削除
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }

        // 修正: Image::findOrFail($id)->delete() は二重クエリのため、取得済みの $image を使用
        $image->delete();

        return redirect()->route('owner.images.index')
            ->with(['message' => '画像を削除しました。', 'status' => 'alert']);
    }
}
