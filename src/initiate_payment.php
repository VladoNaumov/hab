<?php
require_once 'config.php';

// Генерация заголовков авторизации
function generate_auth_headers($method, $path, $body = null) {
    $timestamp = gmdate('Y-m-d\TH:i:s\Z');
    $nonce = bin2hex(random_bytes(16));
    $content_type = $body ? 'application/json' : '';
    $body_hash = $body ? hash('sha256', json_encode($body)) : '';
    $string_to_sign = "$method\n$path\n$timestamp\n$nonce\n$content_type\n$body_hash";
    $signature = base64_encode(hash_hmac('sha256', $string_to_sign, API_SECRET, true));
    return [
        'Authorization' => "Signature keyId=\"" . API_KEY . "\",algorithm=\"hmac-sha256\",headers=\"(request-target) host date nonce content-type digest\",signature=\"$signature\"",
        'Date' => $timestamp,
        'Nonce' => $nonce,
        'Content-Type' => $content_type,
        'Host' => 'api.sandbox.op.fi'
    ];
}

// Данные платежа
$amount = isset($_POST['amount']) ? (int)($_POST['amount'] * 100) : 1000;
$reference = isset($_POST['reference']) ? $_POST['reference'] : 'ORDER' . time();

$payment_data = [
    'amount' => $amount,
    'currency' => 'EUR',
    'merchantId' => MERCHANT_ID,
    'reference' => $reference,
    'returnUrl' => RETURN_URL,
    'cancelUrl' => CANCEL_URL,
    'notificationUrl' => NOTIFICATION_URL
];

// Логирование запроса
file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " Request: " . json_encode($payment_data) . "\n", FILE_APPEND);

// Использование cURL
$ch = curl_init(BASE_URL . '/payments');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array_map(function($k, $v) { return "$k: $v"; }, array_keys(generate_auth_headers('POST', '/payments/v1/payments', $payment_data)), array_values(generate_auth_headers('POST', '/payments/v1/payments', $payment_data))));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_VERBOSE, true);
$verbose = fopen(__DIR__ . '/curl_verbose.log', 'a');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($response === false) {
    $error = curl_error($ch);
    file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " Curl Error: $error\n", FILE_APPEND);
}
fclose($verbose);
curl_close($ch);

// Логирование ответа
file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " HTTP Code: $http_code, Response: " . var_export($response, true) . "\n", FILE_APPEND);

// Проверка ответа
if ($http_code == 201) {
    $payment_info = json_decode($response, true);
    if (isset($payment_info['href'])) {
        header("Location: " . $payment_info['href']);
        exit;
    } else {
        file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " Error: No href in response\n", FILE_APPEND);
        echo "Ошибка: Некорректный ответ API";
    }
} else {
    file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " Error: HTTP $http_code\n", FILE_APPEND);
    echo "Ошибка: $http_code, $response";
}
?>