<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'session_id',
        'total',
        'status',
        'first_name',
        'last_name',
        'email',
        'city',
        'postal_code',
        'address',
        'phone',
        'order_number'
    ];

    /**
     * Связь с элементами заказа.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Использовать поле order_number для маршрутов
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'order_number'; // Используем order_number как ключ маршрута
    }
}
/*

### Объяснение:
1. **`id()`**: Автоинкрементный первичный ключ `id` (BIGINT, unsigned) для уникальной идентификации заказа.
2. **`string('first_name')`**: Хранит имя покупателя, например, "Иван".
3. **`string('last_name')`**: Хранит фамилию покупателя, например, "Петров".
4. **`string('city')`**: Хранит город доставки, например, "Москва".
5. **`string('postal_code')`**: Хранит почтовый индекс, например, "101000".
6. **`string('address')`**: Хранит полный адрес доставки, например, "ул. Ленина, д. 10, кв. 5".
7. **`string('phone')`**: Хранит номер телефона покупателя, например, "+79991234567".
8. **`decimal('total', 8, 2)`**: Хранит общую сумму заказа с двумя знаками после запятой, например, 1250.50.
9. **`json('items')`**: Хранит информацию о товарах в заказе в формате JSON, например, `[{"product_id": 1, "quantity": 2, "price": 500.00}]`.
10. **`string('status')->default('pending')`**: Хранит статус заказа, по умолчанию "pending" (в ожидании). Может меняться, например, на "shipped" или "completed".
11. **`timestamps()`**: Добавляет поля `created_at` и `updated_at` для учета времени создания и последнего обновления заказа.

Эта таблица предназначена для хранения информации о заказах в интернет-магазине, включая данные покупателя, детали доставки, сумму и состав заказа.
*/
