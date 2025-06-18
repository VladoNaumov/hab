<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'article',
        'name',
        'brand',
        'category',
        'description',
        'delivery',
        'country',
        'price',
        'discount_price',
        'image'
    ];

    /**
     * Связь с отзывами
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Аксессор для рейтинга товара
     *
     * @return float
     */
    public function getRatingAttribute(): float
    {
        return $this->reviews()->average('rating') ?? 0.0;
    }

    /**
     * Использовать поле brand для маршрутов
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'brand';
    }

    /**
     * Мутатор для поля brand
     *
     * @param string $value
     * @return void
     */
    public function setBrandAttribute(string $value): void
    {
        // Конверсия в строчные буквы и валидация
        $value = strtolower($value);
        if (!preg_match('/^[a-z0-9-]{1,100}$/', $value)) {
            throw new \InvalidArgumentException('Brand must contain only lowercase Latin letters, numbers, or hyphens, and be up to 100 characters.');
        }
        $this->attributes['brand'] = $value;
    }
}
/*
### Объяснение:
1. **`id()`**: Создает автоинкрементный первичный ключ `id` (BIGINT, unsigned).
2. **`string('name')`**: Хранит название продукта, например, "Смартфон XYZ".
3. **`string('brand')`**: Хранит бренд, например, "Apple" или "Samsung".
4. **`string('category')`**: Указывает категорию, например, "Электроника" или "Одежда".
5. **`text('description')->nullable()`**: Хранит описание продукта, тип TEXT для длинных текстов, может быть пустым.
6. **`string('country')`**: Хранит страну производства, например, "China" или "USA".
7. **`decimal('price', 10, 2)`**: Хранит цену с двумя знаками после запятой (например, 599.99).
8. **`decimal('discount_price', 10, 2)->nullable()`**: Хранит цену со скидкой, может быть пустым, если скидки нет.
9. **`decimal('rating', 3, 2)->default(0)`**: Хранит рейтинг (например, 4.50), по умолчанию 0.
10. **`string('image')->nullable()`**: Хранит путь к файлу изображения, например, "images/product.jpg", может быть пустым.
11. **`timestamps()`**: Добавляет `created_at` и `updated_at` для автоматического учета времени создания и обновления.

Этот код создает таблицу `products` в базе данных с указанными полями, подходящими для хранения информации о продуктах в интернет-магазине.
*/
