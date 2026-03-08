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

    /**
     * ソート順を適用するスコープ（指摘#7 修正済み）
     * 【指摘】想定外の $sortOrder が渡された場合、関数末尾で return がなく
     * 暗黙的に null を返しチェーンが切れてエラーになる。末尾に return $query; を追加。
     */
    public function scopeSortOrder($query, $sortOrder)
    {
        if($sortOrder === null || $sortOrder === \Constant::SORT_ORDER['recommend']){
            return $query->orderBy('sort_order', 'asc') ;
        }
        if($sortOrder === \Constant::SORT_ORDER['higherPrice']){
            return $query->orderBy('price', 'desc') ;
        }
        if($sortOrder === \Constant::SORT_ORDER['lowerPrice']){
            return $query->orderBy('price', 'asc') ;
        }
        if($sortOrder === \Constant::SORT_ORDER['later']){
            return $query->orderBy('products.created_at', 'desc') ;
        }
        if($sortOrder === \Constant::SORT_ORDER['older']){
            return $query->orderBy('products.created_at', 'asc') ;
        }

        return $query;
    }

    /**
     * カテゴリで絞り込むスコープ（指摘#7 修正済み）
     * 【指摘】else で return; だと null を返しメソッドチェーンが切れる。
     * return $query; に修正し、条件に合致しない場合もクエリを返す。
     */
    public function scopeSelectCategory($query, $categoryId)
    {
        if($categoryId != '0'){
            return $query->where('secondary_category_id', $categoryId);
        } else {
            return $query;
        }
    }

    /**
     * キーワード検索スコープ（指摘#7 修正済み）
     * 【指摘】else で return; だと null を返しメソッドチェーンが切れる。
     * return $query; に修正。
     */
    public function scopeSearchKeyword($query, $keyword)
    {
        if (!is_null($keyword)) {
            //全角スペースを半角スペースに変換
            $spaceConvert = mb_convert_kana($keyword, 's');

            //空白で区切って配列にする
            $keywords = preg_split('/[\s]+/', $spaceConvert, -1, PREG_SPLIT_NO_EMPTY);

            //配列の要素を１つずつ取り出して、where句のAND検索で検索
            foreach ($keywords as $word) {
                $query->where('products.name', 'like', '%' . $word . '%');
            }

            return $query;
        } else {
            return $query;  // 修正: return; → return $query;
        }
    }
}
