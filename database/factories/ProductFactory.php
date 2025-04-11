<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'id' => $this->faker->uuid,
            'sku' => 'PROD-' . $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->words(3, true),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'stock' => $this->faker->numberBetween(0, 100),
            'category_id' => Category::factory(),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
} 