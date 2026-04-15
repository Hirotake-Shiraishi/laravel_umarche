<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Order（注文ヘッダ）モデル
 */
class Order extends Model
{
    use HasFactory;

    /**
     * 一括代入（create / fill）で書き込みを許可するカラム
     *
     * $fillable に列挙した属性だけが更新可能。
     *  - 悪意あるリクエストで大量代入されても更新されない。
     */
    protected $fillable = [
        'user_id',
        'stripe_session_id',
        'total_price',
        'status',
    ];

    /**
     * この注文を出したユーザー（多対1）
     *
     * belongsTo: 「子（orders）が親（users）の id を持つ」関係。
     * 使い方例: $order->user->name
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * この注文に含まれる明細行（1対多）
     *
     * hasMany: 「親（orders）に対して子（order_items）が複数」。
     * 使い方例: $order->orderItems
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
