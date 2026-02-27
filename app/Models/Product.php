<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shop;
use App\Models\secondaryCategory;
use App\Models\Image;
use App\Models\Stock;
use App\Models\User;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'name',
        'information',
        'price',
        'is_selling',
        'sort_order',
        'secondary_category_id',
        'image1',
        'image2',
        'image3',
        'image4',
    ];

    // 一対多のリレーション　子モデル -> 親モデル
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function secondaryCategory()
    {
        return $this->belongsTo(SecondaryCategory::class);
    }

    public function imageFirst()
    {
        return $this->belongsTo(Image::class, 'image1');
    }
    public function imageSecond()
    {
        return $this->belongsTo(Image::class, 'image2');
    }
    public function imageThird()
    {
        return $this->belongsTo(Image::class, 'image3');
    }
    public function imageFourth()
    {
        return $this->belongsTo(Image::class, 'image4');
    }

    // 一対多のリレーション　親モデル -> 子モデル
    public function stock()
    {
        return $this->hasMany(Stock::class);
    }

    // 多対多のリレーション　正の関係
    public function users()
    {
        // 第2引数で中間テーブル名
        // デフォルトでは、関連付けるカラム(user_id と product_id)のみ取得。
        // 中間テーブルのカラム取得。
        return $this->belongsToMany(User::class, 'carts')
            ->withPivot(['id', 'quantity']);
    }
}
