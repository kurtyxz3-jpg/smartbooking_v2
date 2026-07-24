<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if (!function_exists('curl_init')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'status' => 'curl_disabled',
        'message' => 'PHP cURL is disabled.',
    ]);
    exit;
}

$curl = curl_init('http://127.0.0.1:5000/api/health');

curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 2,
    CURLOPT_TIMEOUT => 4,
]);

$responseBody = curl_exec($curl);
$curlError = curl_error($curl);
$statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($responseBody === false || $curlError !== '' || $statusCode !== 200) {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'status' => 'offline',
        'message' => 'Python AI server is offline.',
    ]);
    exit;
}

$response = json_decode($responseBody, true);

echo json_encode([
    'success' => true,
    'status' => 'online',
    'provider' => $response['provider'] ?? 'unknown',
    'message' => 'Python AI server is online.',
]);
