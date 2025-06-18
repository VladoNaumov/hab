<?php

// OrderController - контроллер заказов
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Отображает страницу заказа
     *
     * @param Request $request
     * @param string $order_number
     * @return \Illuminate\View\View
     */
    public function show(Request $request, string $order_number)
    {
        // Улучшенное логирование
        $currentSessionId = $request->session()->getId();
        Log::info('Accessing order', [
            'order_number' => $order_number,
            'current_session_id' => $currentSessionId,
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
        ]);

        //  Поиск заказа без учёта регистра
        $order = Order::whereRaw('LOWER(order_number) = ?', [strtolower($order_number)])->first();

        //  Проверяем, найден ли заказ
        if (!$order) {
            Log::warning('Order not found', [
                'order_number' => $order_number,
                'ip' => $request->ip(),
            ]);
            abort(404, 'Order not found for number: ' . htmlspecialchars($order_number));
        }

        // Проверяем совпадение session_id
        if ($order->session_id !== $currentSessionId) {
            Log::warning('Unauthorized access attempt', [
                'order_number' => $order_number,
                'order_session_id' => $order->session_id,
                'current_session_id' => $currentSessionId,
                'ip' => $request->ip(),
            ]);
            abort(404, 'Unauthorized access to order');
        }

        // Загружаем связанные элементы заказа
        $order->load('orderItems.product');

        return view('order.show', compact('order'));
    }
}
