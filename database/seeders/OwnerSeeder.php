<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
//DBファサードとHashファサードの読み込み
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('owners')->insert([
            [
                'name' => 'オーナーA',
                'email' => 'owner-a@test.com',
                'password' => Hash::make('password123'),
                'created_at' => '2021/01/01 11:11:11'
            ],
            [
                'name' => 'オーナーB',
                'email' => 'owner-b@test.com',
                'password' => Hash::make('password123'),
                'created_at' => '2021/01/01 11:11:11'
            ],
            [
                'name' => 'オーナーC',
                'email' => 'owner-c@test.com',
                'password' => Hash::make('password123'),
                'created_at' => '2021/01/01 11:11:11'
            ],
            [
                'name' => 'オーナーD',
                'email' => 'owner-d@test.com',
                'password' => Hash::make('password123'),
                'created_at' => '2021/01/01 11:11:11'
            ],
            [
                'name' => 'オーナーE',
                'email' => 'owner-e@test.com',
                'password' => Hash::make('password123'),
                'created_at' => '2021/01/01 11:11:11'
            ],
            [
                'name' => 'オーナーF',
                'email' => 'owner-f@test.com',
                'password' => Hash::make('password123'),
                'created_at' => '2021/01/01 11:11:11'
            ],
        ]);
    }
}
