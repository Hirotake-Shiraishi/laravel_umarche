<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Owner; //親モデル　Ownerの読み込み
use App\Models\Product;

class Shop extends Model
{
    use HasFactory;

    // $fillable　create/updateメソッドを使用の際、マスアサインメント対策として必須。
    protected $fillable = [
        'owner_id',
        'name',
        'information',
        'filename',
        'is_selling'
    ];

    // ownerメソッドの作成
    // メソッド名は、リレーションするモデル名と対応させる。
    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    // 一対多のリレーション　親モデル -> 子モデル
    public function product()
    {
        return $this->hasMany(Product::class);
    }
}
