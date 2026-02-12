<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// 親モデルのOwnerの読み込み
use App\Models\Owner;

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
}
