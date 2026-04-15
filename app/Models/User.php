<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Product;

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
     * hasMany: users.id が orders.user_id に対応。
     * 使い方例: $user->orders()->latest()->paginate()
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
