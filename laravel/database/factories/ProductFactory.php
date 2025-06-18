<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Основная цена от 5 до 50
        $price = $this->faker->randomFloat(2, 5, 50);

        // Скидочная цена: 30% шанс, что будет меньше основной
        $discount_price = $this->faker->boolean(30)
            ? round($price * $this->faker->randomFloat(2, 0.5, 0.9), 2)
            : null;

        // NEW: Генерируем уникальный brand
        $brand = Str::slug($this->faker->unique()->company, '-');
        $brand = substr($brand, 0, 100); // Ограничиваем длину до 100 символов

        return [
            // Уникальный артикул, например: primer12345-6789
            'article' => 'primer' .
                str_pad($this->faker->unique()->numberBetween(10000, 99999), 5, '0', STR_PAD_LEFT)
                . '-' .
                str_pad($this->faker->numberBetween(1000, 9999), 4, '0', STR_PAD_LEFT),

            // Название из 3 слов и одного дополнительного
            'name' => $this->faker->words(3, true) . ' ' . $this->faker->word,

            // Случайный уникальный бренд в формате a-z0-9-
            'brand' => $brand, // NEW: Уникальный slug

            // Категория
            'category' => $this->faker->randomElement([
                'Footwear', 'Clothing', 'Accessories', 'Electronics', 'Sports Equipment',
            ]),

            //наличия описания
            'description' => $this->faker->paragraph,

            // Страна производства
            'country' => $this->faker->randomElement([
                'USA', 'Germany', 'China', 'Japan', 'Italy', 'France',
            ]),

            // Цена до скидки (всегда <= 50)
            'price' => $price,

            // Цена со скидкой (если есть)
            'discount_price' => $discount_price,

            // Случайное изображение
            'image' => 'images/products/' . $this->faker->randomElement([
                    'product1.jpg',
                    'product2.jpg',
                ]),
        ];
    }
}
