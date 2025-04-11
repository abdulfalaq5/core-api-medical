<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Create Laptop product
        Product::create([
            'id' => '550e8400-e29b-41d4-a716-446655440002',
            'sku' => 'PROD-001',
            'name' => 'Laptop',
            'price' => 1000.00,
            'stock' => 10,
            'category_id' => '550e8400-e29b-41d4-a716-446655440000', // Electronics
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create Smartphone product
        Product::create([
            'id' => '550e8400-e29b-41d4-a716-446655440003',
            'sku' => 'PROD-002',
            'name' => 'Smartphone',
            'price' => 500.00,
            'stock' => 20,
            'category_id' => '550e8400-e29b-41d4-a716-446655440000', // Electronics
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create T-Shirt product
        Product::create([
            'id' => '550e8400-e29b-41d4-a716-446655440004',
            'sku' => 'PROD-003',
            'name' => 'T-Shirt',
            'price' => 20.00,
            'stock' => 50,
            'category_id' => '550e8400-e29b-41d4-a716-446655440001', // Clothing
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
} 