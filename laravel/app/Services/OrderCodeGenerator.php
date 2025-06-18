<?php
// app/Service/OrderCodeGenerator
namespace App\Services;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class OrderCodeGenerator
{
    /**
     * Генерирует уникальный код заказа (например, 20250612ABCD)
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function generateCode(): string
    {
        $date = date('Ymd');
        $microtime = microtime(true);
        $random = mt_rand(0, 9999);
        $uniqueValue = (int) ($microtime * 1000000) + $random;
        $base62 = $this->toBase62($uniqueValue);
        $randomPart = substr($base62, -4);
        $code = $date . $randomPart;

        if (strlen($code) !== 12) {
            throw new InvalidArgumentException('Сгенерированный код заказа имеет неверную длину.');
        }

        Log::info('OrderCodeGenerator order_number:', ['order_number' => $code]);

        return $code;
    }

    /**
     * Преобразует число в base62 (0-9, A-Z, a-z)
     *
     * @param int $number
     * @return string
     */
    protected function toBase62(int $number): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $base = 62;
        $result = '';

        while ($number > 0) {
            $result = $characters[$number % $base] . $result;
            $number = (int) ($number / $base);
        }

        return $result ?: '0';
    }
}
