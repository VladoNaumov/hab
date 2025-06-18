<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Контроллер для управления корзиной покупок.
 * Обрабатывает отображение, добавление, обновление, удаление товаров и создание заказа.
 */
class CartController extends Controller
{
    protected CartService $cartService;
    protected OrderService $orderService;
    protected ResponseService $responseService;

    /**
     * Конструктор контроллера.
     *
     * @param CartService $cartService Сервис корзины.
     * @param OrderService $orderService Сервис заказов.
     * @param ResponseService $responseService Сервис для JSON-ответов.
     */
    public function __construct(CartService $cartService, OrderService $orderService, ResponseService $responseService)
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->responseService = $responseService;
    }

    /**
     * Отображает страницу корзины.
     *
     * @return \Illuminate\View\View Шаблон cart.view.
     */
    public function index()
    {
        // Получаем корзину из сессии
        $cart = session()->get('cart', []);
        // Форматируем данные для шаблона
        $formattedCart = $this->cartService->formatCartForView($cart);
        // Логируем загрузку страницы
        $this->cartService->logCartAction('info', 'Loading cart page', [
            'cart_count' => $formattedCart['count'],
            'cart_total' => $formattedCart['total'],
        ]);
        // Возвращаем шаблон с данными
        return view('cart.view', [
            'cart' => $formattedCart['items'],
            'cartTotal' => $formattedCart['total'],
        ]);
    }

    /**
     * Отображает страницу оформления заказа.
     *
     * @return \Illuminate\View\View Шаблон checkout.create.
     */
    public function create()
    {
        // Получаем корзину из сессии
        $cart = session()->get('cart', []);
        // Форматируем данные для шаблона
        $formattedCart = $this->cartService->formatCartForView($cart);
        // Логируем загрузку страницы
        $this->cartService->logCartAction('info', 'Loading checkout page', [
            'cart_count' => $formattedCart['count'],
            'cart_total' => $formattedCart['total'],
        ]);
        // Возвращаем шаблон с данными
        return view('checkout.create', [
            'cartArray' => $formattedCart['items'],
            'total' => $formattedCart['total'],
        ]);
    }

    /**
     * Добавляет товар в корзину.
     *
     * @param Request $request HTTP-запрос.
     * @param string $brand Бренд товара.
     * @return \Illuminate\Http\RedirectResponse Редирект с сообщением.
     */
    public function add(Request $request, string $brand)
    {
        $quantity = (int) $request->input('quantity', 1);
        try {
            // Валидируем количество
            $this->cartService->validateQuantity($quantity, config('shop.max_quantity_per_item', 10));
            // Ищем товар по бренду
            $product = Product::where('brand', $brand)->first();
            if (!$product) {
                $this->cartService->logCartAction('warning', 'Product not found by brand', ['brand' => $brand]);
                return redirect()->back()->with('error', 'Product not found.');
            }
            // Добавляем товар в корзину
            $cart = session()->get('cart', []);
            $cart = $this->cartService->addToCart($product, $quantity, $cart);
            session()->put('cart', $cart);
            // Возвращаем редирект с успехом
            return redirect()->route('cart.index')->with('success', 'Product added to cart');
        } catch (InvalidArgumentException $e) {
            // Логируем ошибку
            $this->cartService->logCartAction('error', 'Failed to add product to cart', [
                'brand' => $brand,
                'quantity' => $quantity,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Удаляет товар из корзины.
     *
     * @param Request $request HTTP-запрос.
     * @param string $id ID товара.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse Редирект или JSON для AJAX.
     */
    public function remove(Request $request, string $id)
    {
        try {
            // Получаем корзину
            $cart = session()->get('cart', []);
            if (empty($cart)) {
                $this->cartService->logCartAction('warning', 'Cart is empty when trying to remove product', ['product_id' => $id]);
                if ($request->expectsJson()) {
                    return $this->responseService->errorResponse('Cart is empty.', $cart, 0, 0, 400);
                }
                return redirect()->route('cart.index')->with('error', 'Cart is empty.');
            }
            // Удаляем товар
            $cart = $this->cartService->removeFromCart($id, $cart);
            session()->put('cart', $cart);
            // Форматируем корзину для AJAX
            if ($request->expectsJson()) {
                $formattedCart = $this->cartService->formatCartForView($cart);
                return $this->responseService->successResponse('Product removed from cart.', $cart, $formattedCart['total'], $formattedCart['count']);
            }
            // Возвращаем редирект
            return redirect()->route('cart.index')->with('success', 'Product removed from cart');
        } catch (InvalidArgumentException $e) {
            // Логируем ошибку
            $this->cartService->logCartAction('error', 'Failed to remove product from cart', [
                'product_id' => $id,
                'cart_keys' => array_keys($cart),
                'error' => $e->getMessage(),
            ]);
            if ($request->expectsJson()) {
                return $this->responseService->errorResponse('Failed to remove product: ' . $e->getMessage(), $cart, $this->cartService->calculateTotal($cart), count($cart), 400);
            }
            return redirect()->route('cart.index')->with('error', 'Failed to remove product: ' . $e->getMessage());
        } catch (\Exception $e) {
            // Логируем неожиданную ошибку
            $this->cartService->logCartAction('error', 'Unexpected error while removing product from cart', [
                'product_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            if ($request->expectsJson()) {
                return $this->responseService->errorResponse('An unexpected error occurred.', $cart, $this->cartService->calculateTotal($cart), count($cart), 500);
            }
            return redirect()->route('cart.index')->with('error', 'An unexpected error occurred.');
        }
    }

    /**
     * Обновляет количество товара в корзине (для AJAX).
     *
     * @param Request $request HTTP-запрос.
     * @param string $id ID товара.
     * @return \Illuminate\Http\JsonResponse JSON-ответ.
     */
    public function update(Request $request, string $id)
    {
        // Валидируем входные данные
        $validated = $request->validate(['quantity' => 'required|integer|min:1|max:10']);
        try {
            // Обновляем корзину
            $cart = session()->get('cart', []);
            $cart = $this->cartService->updateCartItem($id, $validated['quantity'], $cart);
            session()->put('cart', $cart);
            // Форматируем корзину для ответа
            $formattedCart = $this->cartService->formatCartForView($cart);
            // Возвращаем успешный JSON
            return $this->responseService->successResponse('Quantity updated successfully.', $cart, $formattedCart['total'], $formattedCart['count'], [
                'item_total' => $formattedCart['items'][$id]['total'] ?? 0,
            ]);
        } catch (InvalidArgumentException $e) {
            // Логируем ошибку
            $cart = session()->get('cart', []);
            $this->cartService->logCartAction('error', 'Failed to update product quantity in cart', [
                'product_id' => $id,
                'quantity' => $validated['quantity'],
                'error' => $e->getMessage(),
            ]);
            return $this->responseService->errorResponse('Failed to update quantity: ' . $e->getMessage(), $cart, $this->cartService->calculateTotal($cart), count($cart), 400);
        } catch (\Exception $e) {
            // Логируем неожиданную ошибку
            $cart = session()->get('cart', []);
            $this->cartService->logCartAction('error', 'Unexpected error while updating product quantity', [
                'product_id' => $id,
                'quantity' => $validated['quantity'],
                'error' => $e->getMessage(),
            ]);
            return $this->responseService->errorResponse('An unexpected error occurred.', $cart, $this->cartService->calculateTotal($cart), count($cart), 500);
        }
    }

    /**
     * Создает заказ на основе корзины.
     *
     * @param Request $request HTTP-запрос.
     * @return \Illuminate\Http\RedirectResponse Редирект с сообщением.
     */
    public function store(Request $request)
    {
        // Валидируем пользовательские данные
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        try {
            // Создаем заказ
            $order = $this->orderService->createOrder($request, $validated);
            // Редирект на страницу заказа
            return redirect()->route('orders.show', $order->order_number);
        } catch (InvalidArgumentException $e) {
            // Возвращаем ошибку
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
