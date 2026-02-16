<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Owner;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'filename',
    ];

    // ownerメソッドの作成
    // メソッド名は、リレーションするモデル名と対応させる。
    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }
}
