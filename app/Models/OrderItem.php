<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * OrderItem（注文明細）モデル
 *
 * 1行 = 「ある注文の中の、ある商品が何個、いくらで買われたか」。
 */
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
    ];

    /**
     * 属している注文
     *
     * belongsTo(Order::class): order_items.order_id → orders.id
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * 購入された商品マスタ（名前表示などに使用）
     *
     * 単価は order_items.price（スナップショット）を表示用に使い、
     * 商品名は products を参照（商品が削除されていない前提）。
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
