<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Create Electronics category
        Category::create([
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'Electronics',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create Clothing category
        Category::create([
            'id' => '550e8400-e29b-41d4-a716-446655440001',
            'name' => 'Clothing',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
} 