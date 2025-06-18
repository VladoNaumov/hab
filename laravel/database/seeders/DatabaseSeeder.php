<?php

namespace Database\Seeders;


use App\Models\Product;  // Импорт модели Product
use App\Models\Review;   // Импорт модели Review
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Создаём 100 продуктов
        Product::factory(100)->create();

        // Создаём 100 отзывов для существующих продуктов
        Review::factory(100)->create();
    }
}
