<?php
require_once 'config.php';

$type = isset($_GET['type']) ? $_GET['type'] : '';

if ($type === 'success') {
    echo "<h1>Платеж успешен!</h1>";
    echo "<p>Детали: " . htmlspecialchars(json_encode($_GET)) . "</p>";
} elseif ($type === 'cancel') {
    echo "<h1>Платеж отменен</h1>";
    echo "<p>Детали: " . htmlspecialchars(json_encode($_GET)) . "</p>";
} elseif ($type === 'notify') {
    $input = file_get_contents('php://input');
    $notification = json_decode($input, true);
    file_put_contents(__DIR__ . '/notifications.log', date('Y-m-d H:i:s') . " Notification: " . json_encode($notification) . PHP_EOL, FILE_APPEND);
    http_response_code(200);
} else {
    echo "<h1>Неизвестный запрос</h1>";
}
?>