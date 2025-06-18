<?php

namespace App\Services;

/**
 * Сервис для формирования JSON-ответов для AJAX-запросов.
 */
class ResponseService
{
    /**
     * Формирует успешный JSON-ответ.
     *
     * @param string $message Сообщение об успехе.
     * @param array $cart Данные корзины.
     * @param float $cartTotal Общая сумма корзины.
     * @param int $cartCount Количество товаров.
     * @param array $additionalData Дополнительные данные для ответа.
     * @return \Illuminate\Http\JsonResponse JSON-ответ.
     */
    public function successResponse(string $message, array $cart, float $cartTotal, int $cartCount, array $additionalData = []): \Illuminate\Http\JsonResponse
    {
        return response()->json(array_merge([
            'status' => 'success',
            'message' => $message,
            'cart_count' => $cartCount,
            'cart_total' => $cartTotal,
        ], $additionalData));
    }

    /**
     * Формирует JSON-ответ с ошибкой.
     *
     * @param string $message Сообщение об ошибке.
     * @param array $cart Данные корзины.
     * @param float $cartTotal Общая сумма корзины.
     * @param int $cartCount Количество товаров.
     * @param int $statusCode HTTP-код ответа.
     * @return \Illuminate\Http\JsonResponse JSON-ответ.
     */
    public function errorResponse(string $message, array $cart, float $cartTotal, int $cartCount, int $statusCode = 400): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'cart_count' => $cartCount,
            'cart_total' => $cartTotal,
        ], $statusCode);
    }
}
