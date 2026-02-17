<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run()
    {
        DB::table('primary_categories')->insert([
            [
                'name' => '食品',
                'sort_order' => 1,
            ],
            [
                'name' => 'ペット',
                'sort_order' => 2,
            ],
            [
                'name' => '雑貨',
                'sort_order' => 3,
            ],
        ]);

        DB::table('secondary_categories')->insert([
            [
                'name' => 'お菓子',
                'sort_order' => 1,
                'primary_category_id' => 1
            ],
            [
                'name' => '果物',
                'sort_order' => 2,
                'primary_category_id' => 1
            ],
            [
                'name' => '飲料',
                'sort_order' => 3,
                'primary_category_id' => 1
            ],
            [
                'name' => '犬',
                'sort_order' => 4,
                'primary_category_id' => 2
            ],
            [
                'name' => '猫',
                'sort_order' => 5,
                'primary_category_id' => 2
            ],
            [
                'name' => 'その他',
                'sort_order' => 6,
                'primary_category_id' => 2
            ],
            [
                'name' => 'インテリア雑貨',
                'sort_order' => 7,
                'primary_category_id' => 3
            ],
            [
                'name' => 'ホビー雑貨',
                'sort_order' => 8,
                'primary_category_id' => 3
            ],
            [
                'name' => '文房具',
                'sort_order' => 9,
                'primary_category_id' => 3
            ],


        ]);
    }
}
