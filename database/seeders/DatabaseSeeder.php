<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            AdminSeeder::class,
            OwnerSeeder::class,
            ShopSeeder::class,
            ImageSeeder::class,
            CategorySeeder::class,
            // ProductSeeder::class,
            // StockSeeder::class,
            UserSeeder::class,
        ]);
    }
}
