<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Image;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UploadImageRequest;
use App\Services\ImageService;

class ImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:owners');

        // クロージャを使ったコントローラミドルウェア
        $this->middleware(function ($request, $next) {

            $id = $request->route()->parameter('images'); //URLパラメータ imagesのid取得

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


    public function destroy($id)
    {
        //
    }
}
