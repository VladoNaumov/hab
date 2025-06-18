<?php
$host = 'api.sandbox.op.fi';
$ip = gethostbyname($host);
echo "DNS Resolution for $host: " . ($ip === $host ? "Failed" : $ip) . "\n";

$ch = curl_init('https://api.sandbox.op.fi/payments/v1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Host: api.sandbox.op.fi']);
curl_setopt($ch, CURLOPT_VERBOSE, true);
$verbose = fopen(__DIR__ . '/curl_verbose.log', 'a');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
fclose($verbose);
curl_close($ch);

echo "HTTP Code: $http_code\nResponse: " . var_export($response, true) . "\nError: $error\n";

file_put_contents(__DIR__ . '/test_dns.log', date('Y-m-d H:i:s') . " DNS: $ip, HTTP Code: $http_code, Response: " . var_export($response, true) . ", Error: $error\n", FILE_APPEND);
?>