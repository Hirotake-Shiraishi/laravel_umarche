<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stock;


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

        // productの中で、Shop/Image/Category の外部キーを設定しているため、
        // Shop/Image/Category を先に実行しないとデータが存在しないため、エラーとなる。

        // StockFactoryで、「'product_id' => Product::factory()」を呼び出しているので、
        // Product::factory(100)->create(); は不要。
        Stock::factory(100)->create();
    }
}
