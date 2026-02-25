<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

class StockFactory extends Factory
{
    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'type' => $this->faker->numberBetween(1,2),
            'quantity' => $this->faker->randomNumber,
        ];
    }
}
