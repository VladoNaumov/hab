<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\OrderCodeGenerator;

use App\Models\Order;
use App\Models\OrderItem;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    protected CartService $cartService;
    protected OrderCodeGenerator $codeGenerator;

    public function __construct(CartService $cartService, OrderCodeGenerator $codeGenerator)
    {
        $this->cartService = $cartService;
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * Проверка корзины перед оформлением заказа
     */
    protected function validateCart(array $cart, Request $request): array
    {
        if (empty($cart)) {
            Log::warning('Попытка оформить заказ с пустой корзиной', [
                'ip' => $request->ip(),
                'session_id' => Session::getId(),
            ]);
            return ['valid' => false, 'cartItems' => [], 'error' => 'Корзина пуста'];
        }

        if (count($cart) > 50) {
            Log::warning('Превышение лимита товаров в корзине', [
                'ip' => $request->ip(),
                'session_id' => Session::getId(),
                'count' => count($cart),
            ]);
            return ['valid' => false, 'cartItems' => [], 'error' => 'Максимум 50 товаров в заказе'];
        }

        // Получение актуальных данных о товарах
        $cartItems = $this->cartService->getCartItemsWithDbData($cart, true);
        if (empty($cartItems)) {
            Session::forget('cart');
            Log::warning('Невалидные товары в корзине при оформлении заказа', [
                'ip' => $request->ip(),
                'session_id' => Session::getId(),
                'cart' => $cart,
            ]);
            return ['valid' => false, 'cartItems' => [], 'error' => 'Корзина пуста или содержит невалидные товары'];
        }

        return ['valid' => true, 'cartItems' => $cartItems, 'error' => null];
    }

    /**
     * Отображение формы оформления заказа
     */
    public function create(Request $request)
    {
        $cart = Session::get('cart', []);
        $cartValidation = $this->validateCart($cart, $request);

        if (!$cartValidation['valid']) {
            return redirect()->route('products.index')->with('error', $cartValidation['error']);
        }

        $cartItems = $cartValidation['cartItems'];
        $total = $this->cartService->calculateTotal($cart);

        $cartArray = array_map(function ($item) {
            $itemArray = $item->toArray();
            $itemArray['total'] = $item->getTotalPrice();
            return $itemArray;
        }, $cartItems);

        return view('checkout.create', compact('cartArray', 'total'));
    }

    /**
     * Обработка отправки формы и сохранение заказа
     */
    public function store(Request $request)
    {
        // Валидация формы
        try {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'city' => 'required|string|max:255',
                'postal_code' => 'required|string|max:20',
                'address' => 'required|string|max:255',
                'phone' => [
                    'nullable',
                    'string',
                    'regex:/^\+?[0-9\s\-\(\)]{10,15}$/',
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Ошибка валидации при оформлении заказа', [
                'ip' => $request->ip(),
                'session_id' => Session::getId(),
                'errors' => $e->errors(),
            ]);
            return redirect()->route('checkout.create')->withInput()->with('error', 'Некорректные данные. Пожалуйста, проверьте форму.');
        }

        // Проверка корзины
        $cart = Session::get('cart', []);
        $cartValidation = $this->validateCart($cart, $request);
        if (!$cartValidation['valid']) {
            return redirect()->route('products.index')->with('error', $cartValidation['error']);
        }

        $cartItems = $cartValidation['cartItems'];

        // Создание заказа в транзакции
        try {
            $order = DB::transaction(function () use ($request, $cartItems, $cart) {
                $orderCode = $this->codeGenerator->generateCode();
                $total = $this->cartService->calculateTotal($cart);

                $order = Order::create([
                    'session_id'   => Session::getId(),
                    'first_name'   => $request->first_name,
                    'last_name'    => $request->last_name,
                    'email'        => $request->email,
                    'city'         => $request->city,
                    'postal_code'  => $request->postal_code,
                    'address'      => $request->address,
                    'phone'        => $request->phone,
                    'total'        => $total,
                    'order_number' => $orderCode,
                ]);

                foreach ($cartItems as $item) {
                    OrderItem::create([
                        'order_id'       => $order->id,
                        'product_id'     => $item->productId,
                        'quantity'       => $item->quantity,
                        'price'          => $item->price,
                        'discount_price' => $item->discountPrice,
                    ]);
                }

                return $order;
            });

            // Очистка корзины
            Session::forget('cart');

            Log::info('Заказ успешно создан', [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'total'        => $order->total,
                'session_id'   => Session::getId(),
                'ip'           => $request->ip(),
            ]);

            return redirect()->route('orders.show', $order)->with('success', 'Ваш заказ успешно оформлен.');
        } catch (\Exception $e) {
            Log::error('Ошибка при создании заказа', [
                'error'      => $e->getMessage(),
                'session_id' => Session::getId(),
                'ip'         => $request->ip(),
            ]);
            return redirect()->route('checkout.create')->with('error', 'Ошибка оформления заказа. Пожалуйста, попробуйте снова.');
        }
    }
}
