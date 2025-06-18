<?php
// app/Service/OrderCodeGenerator

namespace App\Services;

use InvalidArgumentException;
use Illuminate\Support\Facades\Log;

class OrderCodeGenerator
{
    protected $dateFunction;
    protected $microtimeFunction;
    protected $randFunction;

    public const CODE_LENGTH = 14; // Увеличено с 12 до 14
    public const BASE62_LENGTH = 6; // Увеличено с 4 до 6

    /**
     * @param callable|null $dateFunction Функция для получения даты (по умолчанию date('Ymd')).
     * @param callable|null $microtimeFunction Функция для получения микровремени (по умолчанию microtime(true)).
     * @param callable|null $randFunction Функция для генерации случайного числа (по умолчанию mt_rand(0, 9999)).
     */
    public function __construct(
        callable $dateFunction = null,
        callable $microtimeFunction = null,
        callable $randFunction = null
    ) {
        $this->dateFunction = $dateFunction ?? fn() => date('Ymd');
        $this->microtimeFunction = $microtimeFunction ?? fn() => microtime(true);
        $this->randFunction = $randFunction ?? fn() => mt_rand(0, 9999);
    }

    /**
     * Генерирует уникальный код заказа.
     *
     * @return string Уникальный код заказа длиной 14 символов.
     * @throws InvalidArgumentException Если длина сгенерированного кода не равна 14 символам.
     */
    public function generateCode(): string
    {
        $date = ($this->dateFunction)();
        $microtime = ($this->microtimeFunction)();
        $rand = ($this->randFunction)();

        $numericPart = (int) ($microtime * 1000000) + $rand;
        $base62Part = $this->toBase62($numericPart);

        // Дополняем нулями слева до 6 символов
        $base62Part = str_pad($base62Part, self::BASE62_LENGTH, '0', STR_PAD_LEFT);
        // Обрезаем до 6 символов, если длиннее
        $base62Part = substr($base62Part, -self::BASE62_LENGTH);

        $code = $date . $base62Part;

        if (strlen($code) !== self::CODE_LENGTH) {
            throw new InvalidArgumentException('Сгенерированный код заказа имеет неверную длину.');
        }

        Log::info('OrderCodeGenerator order_number:', ['order_number' => $code]);

        return $code;
    }

    /**
     * Преобразует число в строку в системе счисления base62.
     *
     * @param int $number Число для преобразования.
     * @return string Результат в base62.
     * @throws InvalidArgumentException Если число отрицательное.
     */
    protected function toBase62(int $number): string
    {
        if ($number < 0) {
            throw new InvalidArgumentException('Отрицательные числа не поддерживаются.');
        }

        if ($number === 0) {
            return '0';
        }

        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $base = 62;
        $result = '';

        while ($number > 0) {
            $remainder = $number % $base;
            $result = $characters[$remainder] . $result;
            $number = (int) ($number / $base);
        }

        return $result;
    }
}
