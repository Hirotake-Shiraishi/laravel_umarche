<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PrimaryCategory;

class secondaryCategory extends Model
{
    use HasFactory;

    // １対多・親モデルへのリレーション
    public function primary()
    {
        return $this->belongsTo(PrimaryCategory::class);
    }
}
