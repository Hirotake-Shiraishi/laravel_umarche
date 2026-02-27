<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Product;

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
}
