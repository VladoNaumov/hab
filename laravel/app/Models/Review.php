<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory; // Добавляем трейт Factory
    protected $fillable = [
        'product_id',
        'rating',
        'comment'
    ];

    /**
     * Связь с товаром
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
/*

### Объяснение:
1. **`id()`**: Автоинкрементный первичный ключ `id` (BIGINT, unsigned) для уникальной идентификации отзыва.
2. **`foreignId('product_id')->constrained('products')->onDelete('cascade')`**: Внешний ключ, связывающий отзыв с продуктом из таблицы `products`. Если продукт удаляется, все связанные отзывы тоже удаляются (`onDelete('cascade')`).
3. **`string('username')`**: Хранит имя пользователя, оставившего отзыв, например, "Иван".
4. **`text('comment')->nullable()`**: Хранит текст отзыва, может быть пустым, если пользователь оставил только рейтинг.
5. **`decimal('rating', 2, 1)`**: Хранит рейтинг отзыва, например, 4.5. Формат позволяет хранить значения от 0.0 до 9.9.
6. **`timestamps()`**: Добавляет поля `created_at` и `updated_at` для учета времени создания и последнего обновления отзыва.

Эта таблица предназначена для хранения отзывов о продуктах в интернет-магазине, включая связь с продуктом, имя пользователя, комментарий и рейтинг.

*/
