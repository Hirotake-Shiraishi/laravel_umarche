<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shop;
use App\Models\secondaryCategory;
use App\Models\Image;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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

    // ローカルスコープ：表示可能（在庫が1以上）の商品を取得
    public function scopeAvailableItems($query)
    {
        $stocks = DB::table('t_stocks')
            ->select('product_id', DB::raw('sum(quantity) as quantity'))
            ->groupBy('product_id')
            ->having('quantity', '>=', 1);

        return $query
            ->joinSub($stocks, 'stock', function($join){
                $join->on('products.id', '=', 'stock.product_id');
            })
            ->join('shops', 'products.shop_id', '=', 'shops.id')
            ->join('secondary_categories', 'products.secondary_category_id', '=','secondary_categories.id')
            ->join('images as image1', 'products.image1', '=', 'image1.id')
            ->where('shops.is_selling', true)
            ->where('products.is_selling', true)
            ->select('products.id as id', 'products.name as name', 'products.price'
                ,'products.sort_order as sort_order'
                ,'products.information', 'secondary_categories.name as category'
                ,'image1.filename as filename');
    }
}
