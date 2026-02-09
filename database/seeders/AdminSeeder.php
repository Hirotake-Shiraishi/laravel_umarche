<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
//DBファサードとHashファサードの読み込み
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admins')->insert([
            'name' => 'test',
            'email' => 'test@test.com',
            'password' => Hash::make('password123'),
            'created_at' => '2026/02/09 11:11:11'
        ]);
    }
}
