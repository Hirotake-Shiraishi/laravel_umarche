<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShopSeeder extends Seeder
{
    public function run()
    {
        DB::table('shops')->insert([
            [
                'owner_id' => 1,
                'name' => '店舗A',
                'information' => 'ここにお店の情報が入ります。ここにお店の情報が入ります。ここにお店の情報が入ります。',
                'filename' => 'sample1.jpg',
                'is_selling' => true
            ],
            [
                'owner_id' => 2,
                'name' => '店舗B',
                'information' => 'ここにお店の情報が入ります。ここにお店の情報が入ります。ここにお店の情報が入ります。',
                'filename' => 'sample2.jpg',
                'is_selling' => true
            ],
            [
                'owner_id' => 3,
                'name' => '店舗C',
                'information' => 'ここにお店の情報が入ります。ここにお店の情報が入ります。ここにお店の情報が入ります。',
                'filename' => 'sample3.jpg',
                'is_selling' => true
            ],
            [
                'owner_id' => 4,
                'name' => '店舗D',
                'information' => 'ここにお店の情報が入ります。ここにお店の情報が入ります。ここにお店の情報が入ります。',
                'filename' => 'sample4.jpg',
                'is_selling' => true
            ],
            [
                'owner_id' => 5,
                'name' => '店舗E',
                'information' => 'ここにお店の情報が入ります。ここにお店の情報が入ります。ここにお店の情報が入ります。',
                'filename' => 'sample5.jpg',
                'is_selling' => true
            ],
            [
                'owner_id' => 6,
                'name' => '店舗F',
                'information' => 'ここにお店の情報が入ります。ここにお店の情報が入ります。ここにお店の情報が入ります。',
                'filename' => 'sample6.jpg',
                'is_selling' => true
            ],
        ]);
    }
}
