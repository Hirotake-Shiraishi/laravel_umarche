<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\secondaryCategory;

class PrimaryCategory extends Model
{
    use HasFactory;

    public function secondary()
    {
        // 一対多のリレーション
        return $this->hasMany(secondaryCategory::class);
    }
}
