<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Product;
use App\Models\Review;
use App\Models\Order;
use App\Models\OrderItem;

/**
 * 一般ユーザー
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    // 多対多のリレーション　正の関係
    public function products()
    {
        // 第2引数で中間テーブル名
        // デフォルトでは、関連付けるカラム(user_id と product_id)のみ取得。
        // 中間テーブルのカラム取得。
        return $this->belongsToMany(Product::class, 'carts')
            ->withPivot(['id', 'quantity']);
    }

    /**
     * このユーザーが行った注文一覧（1対多）
     *
     * 注文履歴画面で「注文一覧」を表示するために使用。
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * このユーザーが投稿したレビュー（1対多）
     *
     * レビュー投稿画面で「すでに投稿済みか」の確認のために使用。
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }


    /**
     * このユーザーが「指定した商品を過去に購入したことがあるか」を判定するメソッド
     *
     * 「購入者だけレビュー投稿可能」にするための根拠を1か所にまとめる。
     *
     * @param  int  $productId  products.id
     * @return bool true=購入済み / false=未購入
     */
    public function hasPurchasedProduct(int $productId): bool
    {
        if ($productId <= 0) {
            return false;
        }

        // order_items → orders の関連を辿って「このユーザーの注文に、この商品が含まれるか」を exists() で判定する。
        // exists() は「1件でもあれば true」を返すため、件数が増えても比較的軽い。
        return OrderItem::query()
            ->where('product_id', $productId)
            ->whereHas('order', function ($query) {
                $query->where('user_id', (int) $this->id);
            })
            ->exists();
    }
}
