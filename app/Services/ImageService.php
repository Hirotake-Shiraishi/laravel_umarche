<?php

namespace App\Services;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    // staticでクラスメソッドを定義すると、インスタンスを生成せずに（クラス名::メソッド名）で直接呼び出すことができる。
    public static function upload($imageFile, $folderName)
    {
        // dd($imageFile['image']);

        if (is_array($imageFile)) {
            $file = $imageFile['image']; // 配列なので[‘key’] を指定し、値を取得
        } else {
            $file = $imageFile;
        }

        $fileName = uniqid(rand() . '_'); //726829743_6990d02b29725
        $extension = $file->extension(); //jpg
        $fileNameToStore = $fileName . '.' . $extension; //726829743_6990d02b29725.jpg

        $resizedImage = Image::make($file)->resize(1920, 1080)->encode();
        Storage::put('public/' . $folderName . '/' . $fileNameToStore, $resizedImage);

        return $fileNameToStore;
    }
}
