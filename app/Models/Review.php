<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\User;

/**
 * Review（商品レビュー）
 *
 * 商品詳細画面で「レビュー一覧」や「平均評価」を表示する。
 * 商品の購入者だけがレビュー投稿できる。
 */
class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'comment',
    ];

    /**
     * レビューが紐づく商品（多対1）
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * レビューを書いたユーザー（多対1）
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
