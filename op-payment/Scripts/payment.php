<?php

require 'vendor/autoload.php';
use Ramsey\Uuid\Uuid;

// --- Конфигурация ---
$apiKey = '';
$merchantId = '71fead42-b248-4fff-4fe8-29296eacaf98';
$privateKeyFile = __DIR__ . '/private_key.pem';
$endpoint = "https://sandbox.apis.op.fi/paymentbutton/v1/payments";
$logFile = __DIR__ . '/api_log.txt';

// --- URL для обратных вызовов ---
$callbackUrl = 'https://webhook.site/81c1c64d-3c9e-493a-8805-c5c31b571054';

// --- ВЫБОР СЦЕНАРИЯ ---
// Доступные значения: 'cancel', 'reject', 'success'

$scenario = 'success';

// --- Выбор accountId по сценарию ---
switch ($scenario) {
    case 'cancel':
        $accountId = "71fead42-b248-4fff-8b86-29296daacaf98";
        break;
    case 'reject':
        $accountId = "eb3b688b-596da-48bd-b2fb-4f3eda501089";
        break;
    case 'success':
        $accountId = "550e8400-e29b-41d4-a716-446655440000";
        break;
    default:
        throw new Exception("Неизвестный сценарий: $scenario");
}

// ---------- Функции ----------

/**
 * Логирование с меткой времени
 */
function logToFile(string $message, string $file): void {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($file, "[$timestamp] $message\n", FILE_APPEND);
}

/**
 * Генерация заголовков с HMAC-подписью
 */
function generateHeaders($apiKey, $merchantId, $privateKeyFile, $endpoint, $bodyJson, $logFile): array {
    $sessionId = Uuid::uuid4()->toString();
    $requestId = Uuid::uuid4()->toString();
    $date = (new DateTime('UTC'))->format('D, d M Y H:i:s \G\M\T');

    $data = "POST\napplication/json\n$date\n$merchantId\n$apiKey\n$sessionId\n$requestId\n$endpoint\n$bodyJson";
    logToFile("Data for HMAC:\n$data\n", $logFile);

    $privateKey = openssl_pkey_get_private("file://$privateKeyFile");
    if (!$privateKey) {
        $error = "Failed to load private key: " . openssl_error_string();
        logToFile($error, $logFile);
        throw new Exception($error);
    }
    logToFile("Private key loaded successfully", $logFile);

    openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    $signature = bin2hex($signature);
    $authorizationHeader = "$merchantId:1:0:$signature";

    return [
        "x-api-key: $apiKey",
        "Authorization: $authorizationHeader",
        "Content-Type: application/json",
        "Date: $date",
        "x-session-id: $sessionId",
        "x-request-id: $requestId"
    ];
}

/**
 * Сборка JSON тела запроса
 */
function buildRequestBody($merchantId, $accountId, $callbackUrl): string {
    $body = [
        'amount' => '1.00',
        'currency' => 'EUR',
        'merchantId' => $merchantId,
        'accountId' => $accountId,
        'return' => ['url' => $callbackUrl],
        'cancel' => ['url' => $callbackUrl],
        'reject' => ['url' => $callbackUrl],
        'b2bBackend' => $callbackUrl,
        'reference' => 'RF' . time()
    ];
    return json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

/**
 * Отправка POST-запроса в OP API
 */
function sendPaymentRequest($endpoint, $headers, $bodyJson, $logFile): void {
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyJson);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // sandbox only
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $curlLog = fopen($logFile, 'a');
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_STDERR, $curlLog);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    fclose($curlLog);

    logToFile("HTTP Code: $httpCode\nResponse: $response\n", $logFile);

    if (curl_errno($ch)) {
        $error = "cURL Error: " . curl_error($ch);
        logToFile($error, $logFile);
        echo "$error\n";
    } else {
        echo "HTTP Code: $httpCode\n";
        echo "Response: $response\n";
    }

    curl_close($ch);
}

// ---------- Основной запуск ----------

try {
    // Строим тело запроса в формате JSON, передавая merchantId, accountId и callbackUrl.
    $bodyJson = buildRequestBody($merchantId, $accountId, $callbackUrl);
    
    // Логируем сформированное тело запроса (bodyJson) в файл для отладки.
    logToFile("Body JSON:\n$bodyJson", $logFile);

    // Генерируем заголовки для запроса с использованием API-ключа, merchantId, privateKey и других данных.
    $headers = generateHeaders($apiKey, $merchantId, $privateKeyFile, $endpoint, $bodyJson, $logFile);
    
    // Логируем заголовки запроса (headers) для отладки.
    logToFile("Headers:\n" . implode("\n", $headers), $logFile);

    // Отправляем запрос на оплату с использованием подготовленных заголовков и тела запроса.
    sendPaymentRequest($endpoint, $headers, $bodyJson, $logFile);
    
} catch (Exception $e) {
    // Если возникла ошибка, выводим её на экран.
    echo "Ошибка: " . $e->getMessage() . "\n";
    
    // Логируем информацию об исключении для дальнейшего анализа.
    logToFile("Exception: " . $e->getMessage(), $logFile);
}

