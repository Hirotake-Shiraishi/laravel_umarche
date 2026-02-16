<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Shop; //外部キーのあるShopモデルの読み込み
use App\Models\Image;

class Owner extends Authenticatable
{
    use HasFactory, SoftDeletes;

        /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // shopメソッドの作成
    // メソッド名は、リレーションするモデル名と対応させる。
    public function shop()
    {
        return $this->hasOne(Shop::class);
    }

    public function image()
    {
        // 一対多のリレーション
        return $this->hasMany(Image::class);
    }
}
