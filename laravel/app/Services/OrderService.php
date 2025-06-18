<?php

namespace App\Services;

use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderCodeGenerator;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Сервис для управления заказами.
 * Обрабатывает создание заказа на основе данных корзины.
 */
class OrderService
{
    protected CartService $cartService;

    /**
     * Конструктор сервиса.
     *
     * @param CartService $cartService Сервис корзины.
     */
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Создает заказ на основе данных корзины и пользовательских данных.
     *
     * @param Request $request HTTP-запрос.
     * @param array $validated Валидированные данные пользователя.
     * @return Order Созданный заказ.
     * @throws InvalidArgumentException Если корзина пуста.
     */
    public function createOrder(Request $request, array $validated): Order
    {
        // Логируем начало создания заказа
        $currentSessionId = $request->session()->getId();
        $this->cartService->logCartAction('info', 'Starting order creation', [
            'session_id' => $currentSessionId,
            'ip' => $request->ip(),
        ]);

        // Проверяем, что корзина не пуста
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            throw new InvalidArgumentException('Cart is empty');
        }

        // Вычисляем общую сумму
        $total = $this->cartService->calculateTotal($cart);

        // Создаем заказ
        $order = Order::create([
            'order_number' => app(OrderCodeGenerator::class)->generate(),
            'session_id' => $currentSessionId,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'city' => $validated['city'],
            'postal_code' => $validated['postal_code'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'total' => $total,
            'status' => 'pending',
        ]);

        // Создаем элементы заказа с использованием коллекции
        collect($this->cartService->getCartItemsWithDbData($cart))
            ->each(function ($item) use ($order) {
                $order->orderItems()->create([
                    'product_id' => $item->productId,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'discount_price' => $item->discountPrice,
                ]);
            });

        // Очищаем корзину
        session()->forget('cart');

        // Логируем успешное создание заказа
        $this->cartService->logCartAction('info', 'Заказ успешно создан', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'total' => $total,
            'ip' => $request->ip(),
            'session_id' => $order->session_id,
            'cart_count' => count($this->cartService->getCartItemsWithDbData($cart)),
        ]);

        // Проверяем, не изменился ли ID сессии
        if ($request->session()->getId() !== $currentSessionId) {
            $this->cartService->logCartAction('warning', 'Session ID changed after order creation', [
                'original_session_id' => $currentSessionId,
                'new_session_id' => $request->session()->getId(),
            ]);
        }

        return $order;
    }
}

// Заменили цикл foreach для создания order_items на collect()->each(), что делает код более декларативным.
