<?php

namespace App\Services;

use InvalidArgumentException;
use Illuminate\Http\JsonResponse;

class ResponseService
{
    /**
     * Формирует успешный JSON-ответ.
     *
     * @param string $message Сообщение об успехе.
     * @param array $cart Корзина с товарами.
     * @param float $cartTotal Общая стоимость корзины.
     * @param int $cartCount Количество товаров.
     * @param array $additionalData Дополнительные данные для ответа.
     * @return \Illuminate\Http\JsonResponse JSON-ответ.
     */
    public function successResponse(
        string $message = '',
        array $cart = [],
        float $cartTotal = 0.0,
        int $cartCount = 0,
        array $additionalData = []
    ): JsonResponse {
        if ($cartCount < 0) {
            throw new InvalidArgumentException('Cart count cannot be negative');
        }

        return response()->json(array_merge([
            'status' => 'success',
            'message' => $message,
            'cart_count' => $cartCount,
            'cart_total' => $cartTotal,
            'cart' => $cart,
        ], $additionalData));
    }

    /**
     * Формирует JSON-ответ с ошибкой.
     *
     * @param string $message Сообщение об ошибке.
     * @param array $cart Корзина с товарами.
     * @param float $cartTotal Общая стоимость корзины.
     * @param int $cartCount Количество товаров.
     * @param int $statusCode Код статуса HTTP.
     * @return \Illuminate\Http\JsonResponse JSON-ответ.
     */
    public function errorResponse(
        string $message = '',
        array $cart = [],
        float $cartTotal = 0.0,
        int $cartCount = 0,
        int $statusCode = 400
    ): JsonResponse {
        if ($cartCount < 0) {
            throw new InvalidArgumentException('Cart count cannot be negative');
        }

        if ($statusCode < 400 || $statusCode > 599) {
            throw new InvalidArgumentException('Status code must be between 400 and 599');
        }

        return response()->json([
            'status' => 'error',
            'message' => $message,
            'cart_count' => $cartCount,
            'cart_total' => $cartTotal,
            'cart' => $cart,
        ], $statusCode);
    }
}
