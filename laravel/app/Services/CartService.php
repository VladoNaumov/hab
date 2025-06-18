<?php

namespace App\Services;

use App\Models\Product;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для управления корзиной покупок.
 * Обрабатывает добавление, обновление, удаление товаров, форматирование данных и логирование.
 */
class CartService
{
    /**
     * Валидирует количество товара.
     *
     * @param int $quantity Количество товара.
     * @param int $maxQuantity Максимально допустимое количество.
     * @throws InvalidArgumentException Если количество некорректно.
     */
    public function validateQuantity(int $quantity, int $maxQuantity): void
    {
        if (!is_int($quantity) || $quantity < 1) {
            throw new InvalidArgumentException('Количество не может быть меньше 1.');
        }
        if ($quantity > $maxQuantity) {
            throw new InvalidArgumentException("Нельзя добавить более {$maxQuantity} единиц одного товара.");
        }
    }

    /**
     * Получает данные корзины с актуальными ценами из базы данных.
     *
     * @param array $cart Данные корзины из сессии.
     * @param bool $cleanSession Очищать ли сессию при некорректных товарах.
     * @return array Массив объектов CartItem с данными товаров.
     */
    public function getCartItemsWithDbData(array $cart, bool $cleanSession = false): array
    {
        // Если корзина пуста, возвращаем пустой массив
        if (empty($cart)) {
            return [];
        }

        // Получаем ID товаров
        $productIds = array_keys($cart);
        // Запрашиваем товары из базы
        $products = Product::whereIn('id', $productIds)
            ->select(['id', 'name', 'price', 'discount_price', 'image', 'description'])
            ->get()
            ->keyBy('id');

        // Преобразуем корзину в коллекцию
        $cartItems = collect($cart)->mapWithKeys(function ($item, $productId) use ($products, $cleanSession) {
            // Проверяем количество
            if (!isset($item['quantity']) || !is_int($item['quantity']) || $item['quantity'] <= 0) {
                $this->logCartAction('warning', 'Некорректное количество в корзине', [
                    'product_id' => $productId,
                    'quantity' => $item['quantity'] ?? 'not set',
                ]);
                if ($cleanSession) {
                    $cart = session('cart', []);
                    unset($cart[$productId]);
                    session(['cart' => $cart]);
                }
                return null;
            }

            // Проверяем товар
            $product = $products->get($productId);
            if (!$product || $product->price <= 0) {
                $this->logCartAction('warning', 'Товар не найден или цена некорректна', [
                    'product_id' => $productId,
                    'price' => $product->price ?? 'not found',
                ]);
                if ($cleanSession) {
                    $cart = session('cart', []);
                    unset($cart[$productId]);
                    session(['cart' => $cart]);
                }
                return null;
            }

            try {
                // Создаем CartItem
                return [
                    $productId => new CartItem(
                        productId: $product->id,
                        name: $product->name,
                        price: (float) $product->price,
                        discountPrice: $product->discount_price ? (float) $product->discount_price : null,
                        quantity: $item['quantity'],
                        image: $product->image,
                        description: $product->description
                    )
                ];
            } catch (InvalidArgumentException $e) {
                // Логируем ошибку
                $this->logCartAction('error', 'Ошибка создания CartItem: ' . $e->getMessage(), [
                    'product_id' => $productId,
                    'name' => $product->name,
                    'price' => $product->price,
                    'discount_price' => $product->discount_price,
                    'quantity' => $item['quantity'],
                    'image' => $product->image,
                    'description' => $product->description,
                    'error' => $e->getMessage(),
                ]);
                if ($cleanSession) {
                    $cart = session('cart', []);
                    unset($cart[$productId]);
                    session(['cart' => $cart]);
                }
                return null;
            }
        })->filter()->toArray();

        // Очищаем сессию, если нет валидных товаров
        if ($cleanSession && empty($cartItems) && !empty($cart)) {
            $this->logCartAction('info', 'Очистка сессии корзины, так как нет валидных товаров');
            session(['cart' => []]);
        }

        return $cartItems;
    }

    /**
     * Вычисляет общую сумму корзины.
     *
     * @param array $cart Данные корзины из сессии.
     * @param bool $format Форматировать ли сумму (с разделителями).
     * @return float|string Общая сумма (число или форматированная строка).
     */
    public function calculateTotal(array $cart, bool $format = false): float|string
    {
        if (empty($cart)) {
            return $format ? number_format(0, 2, ',', ' ') : 0.0;
        }

        $total = collect($this->getCartItemsWithDbData($cart))
            ->map(fn($item) => $item->getTotalPrice())
            ->sum();

        $total = round($total, 2);
        return $format ? number_format($total, 2, ',', ' ') : $total;
    }

    /**
     * Форматирует корзину для отображения в шаблонах.
     *
     * @param array $cart Данные корзины из сессии.
     * @return array Форматированные данные (товары, общая сумма, количество).
     */
    public function formatCartForView(array $cart): array
    {
        $cartItems = collect($this->getCartItemsWithDbData($cart, true));
        $cartArray = $cartItems->mapWithKeys(function ($item) {
            $itemArray = $item->toArray();
            $itemArray['total'] = $item->getTotalPrice();
            return [$item->productId => $itemArray];
        })->toArray();

        return [
            'items' => $cartArray,
            'total' => $this->calculateTotal($cart),
            'count' => $cartItems->count(),
        ];
    }

    /**
     * Логирует действия с корзиной.
     *
     * @param string $level Уровень лога (info, warning, error).
     * @param string $message Сообщение для лога.
     * @param array $context Дополнительные данные для лога.
     */
    public function logCartAction(string $level, string $message, array $context = []): void
    {
        Log::{$level}($message, $context);
    }

    /**
     * Добавляет товар в корзину.
     *
     * @param Product $product Модель товара.
     * @param int $quantity Количество товара.
     * @param array $cart Текущая корзина из сессии.
     * @return array Обновленная корзина.
     * @throws InvalidArgumentException Если данные некорректны.
     */
    public function addToCart(Product $product, int $quantity, array $cart): array
    {
        // Проверяем цену товара
        if ($product->price <= 0) {
            $this->logCartAction('warning', 'Попытка добавить товар с некорректной ценой', [
                'product_id' => $product->id,
                'price' => $product->price,
            ]);
            throw new InvalidArgumentException('Цена товара некорректна.');
        }

        // Валидируем количество
        $maxQuantity = config('shop.max_quantity_per_item', 10);
        $this->validateQuantity($quantity, $maxQuantity);

        $productId = (string) $product->id;

        // Проверяем лимит корзины (50 товаров)
        if (count($cart) >= 50 && !isset($cart[$productId])) {
            $this->logCartAction('warning', 'Попытка превысить лимит корзины (50 товаров)', [
                'product_id' => $productId,
            ]);
            throw new InvalidArgumentException('Корзина переполнена (максимум 50 товаров).');
        }

        // Обновляем или добавляем товар
        if (isset($cart[$productId])) {
            $newQuantity = $cart[$productId]['quantity'] + $quantity;
            $this->validateQuantity($newQuantity, $maxQuantity);
            $cart[$productId]['quantity'] = $newQuantity;
        } else {
            $cart[$productId] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
            ];
        }

        // Логируем добавление
        $this->logCartAction('info', 'Товар добавлен в корзину', [
            'product_id' => $productId,
            'quantity' => $quantity,
            'name' => $product->name,
            'description' => $product->description,
        ]);

        return $cart;
    }

    /**
     * Обновляет количество товара в корзине.
     *
     * @param string $productId ID товара.
     * @param int $quantity Новое количество.
     * @param array $cart Текущая корзина.
     * @return array Обновленная корзина.
     * @throws InvalidArgumentException Если товар не найден или данные некорректны.
     */
    public function updateCartItem(string $productId, int $quantity, array $cart): array
    {
        // Проверяем, есть ли товар в корзине
        if (!isset($cart[$productId])) {
            $this->logCartAction('warning', 'Попытка обновить несуществующий товар в корзине', [
                'product_id' => $productId,
            ]);
            throw new InvalidArgumentException('Товар не найден в корзине.');
        }

        // Валидируем количество
        $maxQuantity = config('shop.max_quantity_per_item', 10);
        $this->validateQuantity($quantity, $maxQuantity);

        // Проверяем товар в базе
        $product = Product::select(['id', 'price'])->find($productId);
        if (!$product || $product->price <= 0) {
            $this->logCartAction('warning', 'Товар не найден или цена некорректна при обновлении', [
                'product_id' => $productId,
                'price' => $product->price ?? 'not found',
            ]);
            throw new InvalidArgumentException('Товар не найден или цена некорректна.');
        }

        // Обновляем количество
        $cart[$productId]['quantity'] = $quantity;
        $this->logCartAction('info', 'Количество товара обновлено', [
            'product_id' => $productId,
            'quantity' => $quantity,
        ]);

        return $cart;
    }

    /**
     * Удаляет товар из корзины.
     *
     * @param string $productId ID товара.
     * @param array $cart Текущая корзина.
     * @return array Обновленная корзина.
     * @throws InvalidArgumentException Если товар не найден.
     */
    public function removeFromCart(string $productId, array $cart): array
    {
        // Проверяем, есть ли товар в корзине
        if (!isset($cart[$productId])) {
            $this->logCartAction('warning', 'Попытка удалить несуществующий товар из корзины', [
                'product_id' => $productId,
            ]);
            throw new InvalidArgumentException('Товар не найден в корзине.');
        }

        // Удаляем товар
        unset($cart[$productId]);
        $this->logCartAction('info', 'Товар удален из корзины', [
            'product_id' => $productId,
        ]);

        return $cart;
    }
}

// Метод getCartItemsWithDbData использует collect()->mapWithKeys()->filter() для обработки корзины.
//
// Метод calculateTotal применяет collect()->map()->sum() для вычисления суммы.
//
// Метод formatCartForView использует collect()->mapWithKeys() и count() для форматирования данных.
