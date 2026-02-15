<?php

namespace App\Services;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    // staticでクラスメソッドを定義すると、インスタンスを生成せずに（クラス名::メソッド名）で直接呼び出すことができる。
    public static function upload($imageFile, $folderName)
    {
        $fileName = uniqid(rand() . '_'); //726829743_6990d02b29725
        $extension = $imageFile->extension(); //jpg
        $fileNameToStore = $fileName . '.' . $extension; //726829743_6990d02b29725.jpg

        $resizedImage = Image::make($imageFile)->resize(1920, 1080)->encode();
        Storage::put('public/' . $folderName . '/' . $fileNameToStore, $resizedImage);

        return $fileNameToStore;
    }
}
