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
                'name' => 'test1',
                'email' => 'test1@test.com',
                'password' => Hash::make('password123'),
                'created_at' => '2026/02/09 11:11:11'
            ],
            [
                'name' => 'test2',
                'email' => 'test2@test.com',
                'password' => Hash::make('password123'),
                'created_at' => '2026/02/09 11:11:11'
            ],
            [
                'name' => 'test3',
                'email' => 'test3@test.com',
                'password' => Hash::make('password123'),
                'created_at' => '2026/02/09 11:11:11'
            ],
            [
                'name' => 'test4',
                'email' => 'test4@test.com',
                'password' => Hash::make('password123'),
                'created_at' => '2026/02/09 11:11:11'
            ],
            [
                'name' => 'test5',
                'email' => 'test5@test.com',
                'password' => Hash::make('password123'),
                'created_at' => '2026/02/09 11:11:11'
            ],
            [
                'name' => 'test6',
                'email' => 'test6@test.com',
                'password' => Hash::make('password123'),
                'created_at' => '2026/02/09 11:11:11'
            ],
            [
                'name' => 'test7',
                'email' => 'test7@test.com',
                'password' => Hash::make('password123'),
                'created_at' => '2026/02/09 11:11:11'
            ],
            [
                'name' => 'test8',
                'email' => 'test8@test.com',
                'password' => Hash::make('password123'),
                'created_at' => '2026/02/09 11:11:11'
            ],
            [
                'name' => 'test9',
                'email' => 'test9@test.com',
                'password' => Hash::make('password123'),
                'created_at' => '2026/02/09 11:11:11'
            ],
            [
                'name' => 'test10',
                'email' => 'test10@test.com',
                'password' => Hash::make('password123'),
                'created_at' => '2026/02/09 11:11:11'
            ],
        ]);
    }
}
