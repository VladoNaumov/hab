<?php
/*
 * Класс CartItem используется в сервисе CartService (например, в методе getCartItemsWithDbData).
 * Он инкапсулирует логику одной позиции в корзине:
 * — обеспечивает валидацию входных данных;
 * — вычисляет финальную цену;
 * — упрощает передачу данных в шаблоны (cart.view, checkout.create) и ответы AJAX (например, в cart.js);
 * — гарантирует, что структура данных единообразна.
 */

namespace App\Services;

use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Представляет одну позицию в корзине покупок.
 * Содержит информацию о товаре, проверяет корректность данных и предоставляет метод для расчёта общей стоимости.
 */
class CartItem
{
    // --- Свойства, представляющие данные о товаре ---
    public int $productId;             // ID товара
    public string $name;               // Название
    public float $price;               // Полная цена без скидки
    public ?float $discountPrice;      // Цена со скидкой (может отсутствовать)
    public int $quantity;              // Количество в корзине
    public ?string $image;             // Ссылка на изображение (опционально)
    public ?string $description;       // Краткое описание товара
    public ?string $article;           // Артикул

    /**
     * Конструктор класса.
     * Проверяет корректность переданных данных и сохраняет их в свойствах объекта.
     *
     * @param int $productId ID товара (> 0).
     * @param string $name Название товара (не пустое).
     * @param float $price Базовая цена (> 0).
     * @param float|null $discountPrice Цена со скидкой (если указана, должна быть > 0 и <= обычной цены).
     * @param int $quantity Количество (> 0).
     * @param string|null $image URL изображения.
     * @param string|null $description Описание.
     * @param string|null $article Артикул.
     *
     * @throws InvalidArgumentException Если одно из условий не соблюдено.
     */
    public function __construct(
        int     $productId,
        string  $name,
        float   $price,
        ?float  $discountPrice,
        int     $quantity,
        ?string $image = null,
        ?string $description = null,
        ?string $article = null
    ) {
        // Валидация входных данных
        if ($productId <= 0) {
            throw new InvalidArgumentException('Product ID must be positive.');
        }

        if (empty(trim($name))) {
            throw new InvalidArgumentException('Product name cannot be empty.');
        }

        if ($price <= 0) {
            throw new InvalidArgumentException('Price must be positive.');
        }

        if ($discountPrice !== null && ($discountPrice <= 0 || $discountPrice > $price)) {
            throw new InvalidArgumentException('Discount price must be positive and less than or equal to price.');
        }

        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be positive.');
        }

        // Присваивание свойств
        $this->productId = $productId;
        $this->name = $name;
        $this->price = $price;
        $this->discountPrice = $discountPrice;
        $this->quantity = $quantity;
        $this->image = $image;
        $this->description = $description;
        $this->article = $article;
    }

    /**
     * Возвращает общую цену товара с учетом количества и скидки (если есть).
     *
     * @return float Общая сумма за товар.
     */
    public function getTotalPrice(): float
    {
        // Используем скидку, если она указана, иначе — обычную цену
        $price = round($this->discountPrice ?? $this->price, 2);

        // Умножаем на количество и округляем до 2 знаков после запятой
        return round($price * $this->quantity, 2);
    }

    /**
     * Преобразует объект в ассоциативный массив.
     * Исключает значения, равные null — это позволяет избежать ненужного шума в JSON/AJAX.
     *
     * @return array Массив с ключами: product_id, name, price, discount_price, quantity и др. (если заданы).
     */
    public function toArray(): array
    {
        return collect([
            'product_id' => $this->productId,
            'name' => $this->name,
            'price' => $this->price,
            'discount_price' => $this->discountPrice,
            'quantity' => $this->quantity,
            'image' => $this->image,
            'description' => $this->description,
            'article' => $this->article,
        ])->filter(fn($value) => $value !== null)->toArray(); // Удаляем null-поля
    }
}
