<?php

// Подключаем Ramsey\Uuid для генерации UUID
require 'vendor/autoload.php';
use Ramsey\Uuid\Uuid;

// --- Ключи и настройки ---
$apiKey = 'Your API Key'; // Ваш sandbox API-ключ
$privateKeyFile = __DIR__ . '/private-key.pem'; // Путь к файлу приватного ключа
$merchantId = '71fead42-b248-4fff-4fe8-29296eacaf98'; // Тестовый merchantId

// Выберите accountId для тестирования
$accountId = "71fead42-b248-4fff-8b86-29296daacaf98"; // Для сценария ОТМЕНЫ
// $accountId = "eb3b688b-596da-48bd-b2fb-4f3eda501089"; // Для сценария ОТКЛОНЕНИЯ
// $accountId = "550e8400-e29b-41d4-a716-446655440000"; // Для успешного платежа

$endpoint = "https://sandbox.apis.op.fi/paymentbutton/v1/payments"; // URL песочницы
$logFile = __DIR__ . '/api_log.txt'; // Путь к файлу логов

// --- Функция для логирования ---
function logToFile($message, $logFile) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// --- Генерируем данные для запроса ---
$sessionId = Uuid::uuid4()->toString(); // Уникальный ID сессии
$requestId = Uuid::uuid4()->toString(); // Уникальный ID запроса
$date = (new DateTime('UTC'))->format('D, d M Y H:i:s \G\M\T'); // Дата в формате RFC 1123 (e.g., Thu, 19 Jun 2025 16:36:00 GMT)

// --- Тело запроса ---
$body = [
    'amount' => '1.00', // Сумма платежа
    'currency' => 'EUR', // Валюта
    'merchantId' => $merchantId,
    'accountId' => $accountId,
    'return' => ['url' => 'https://webhook.site/81c1c64d-3c9e-493a-8805-c5c31b571054'], // URL при успехе
    'b2bBackend' => 'https://webhook.site/81c1c64d-3c9e-493a-8805-c5c31b571054', // Callback URL
    'cancel' => ['url' => 'https://webhook.site/81c1c64d-3c9e-493a-8805-c5c31b571054'], // URL при отмене
    'reject' => ['url' => 'https://webhook.site/81c1c64d-3c9e-493a-8805-c5c31b571054'], // URL при отклонении
    'reference' => 'RF' . time() // Уникальный референс (динамический)
];
$bodyJson = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
logToFile("Body JSON:\n$bodyJson\n", $logFile);

// --- Формируем строку для HMAC-подписи ---
$data = "POST\napplication/json\n$date\n$merchantId\n$apiKey\n$sessionId\n$requestId\n$endpoint\n$bodyJson";
logToFile("Data for HMAC:\n$data\n", $logFile);

// --- Загружаем приватный ключ с проверкой ---
$privateKey = openssl_pkey_get_private("file://$privateKeyFile");
if ($privateKey === false) {
    $error = "Failed to load private key: " . openssl_error_string();
    logToFile($error, $logFile);
    echo $error . "\n";
    exit;
} else {
    logToFile("Private key loaded successfully", $logFile);
    echo "Private key loaded successfully\n";
}

// --- Генерируем HMAC-подпись ---
openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
$signature = bin2hex($signature);
$algorithm = 1; // SHA-256
$keyVersion = 0; // Версия ключа (уточните в документации)
$authorizationHeader = "$merchantId:$algorithm:$keyVersion:$signature";
logToFile("Authorization Header:\n$authorizationHeader\n", $logFile);

// --- Настраиваем заголовки ---
$headers = [
    "x-api-key: $apiKey",
    "Authorization: $authorizationHeader",
    "Content-Type: application/json",
    "Date: $date",
    "x-session-id: $sessionId",
    "x-request-id: $requestId"
];
logToFile("Headers:\n" . implode("\n", $headers) . "\n", $logFile);

// --- Отправляем запрос через cURL ---
$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyJson);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Отключаем проверку SSL для песочницы
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$curlLog = fopen($logFile, 'a');
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_STDERR, $curlLog);

// --- Выполняем запрос ---
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
fclose($curlLog);
logToFile("HTTP Code: $httpCode\nResponse: $response\n", $logFile);

// --- Проверяем ошибки cURL ---
if (curl_errno($ch)) {
    $error = "cURL Error: " . curl_error($ch);
    logToFile($error, $logFile);
    echo "$error\n";
} else {
    echo "HTTP Code: $httpCode\n";
    echo "Response: $response\n";
}

curl_close($ch);

?>