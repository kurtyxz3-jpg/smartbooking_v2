<?php
declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');

if (
    !isset($_SESSION['user_id'])
    || ($_SESSION['role'] ?? '') !== 'customer'
) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Please sign in as a customer.',
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.',
    ]);
    exit;
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody ?: '', true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data.',
    ]);
    exit;
}

$color = trim((string) ($payload['color'] ?? ''));
$style = trim((string) ($payload['style'] ?? ''));
$nailShape = trim((string) ($payload['nail_shape'] ?? 'Almond'));
$nailLength = trim((string) ($payload['nail_length'] ?? 'Medium'));
$occasion = trim((string) ($payload['occasion'] ?? 'Everyday'));
$instructions = trim((string) ($payload['instructions'] ?? ''));
$addOns = $payload['add_ons'] ?? [];

$errors = [];

if ($color === '') {
    $errors['color'] = 'Preferred colors are required.';
}

if ($style === '') {
    $errors['style'] = 'Design style is required.';
}

if (!is_array($addOns)) {
    $errors['add_ons'] = 'Add-ons must be a list.';
    $addOns = [];
}

if (mb_strlen($instructions) > 600) {
    $errors['instructions'] = 'Instructions must not exceed 600 characters.';
}

if ($errors) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Please correct the form.',
        'errors' => $errors,
    ]);
    exit;
}

$pythonPayload = [
    'color' => $color,
    'style' => $style,
    'nail_shape' => $nailShape,
    'nail_length' => $nailLength,
    'occasion' => $occasion,
    'add_ons' => array_values(array_map(
        static fn ($value): string => trim((string) $value),
        array_slice($addOns, 0, 10)
    )),
    'instructions' => $instructions,
];

if (!function_exists('curl_init')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'PHP cURL is not enabled. Enable extension=curl in php.ini and restart Apache.',
    ]);
    exit;
}

$pythonUrl = 'http://127.0.0.1:5000/api/nail-designs/generate';
$curl = curl_init($pythonUrl);

curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode(
        $pythonPayload,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    ),
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 120,
]);

$responseBody = curl_exec($curl);
$curlError = curl_error($curl);
$statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($responseBody === false || $curlError !== '') {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'message' => 'The Python AI server is unavailable. Start it using: py app.py',
        'technical_message' => $curlError,
    ]);
    exit;
}

$response = json_decode($responseBody, true);

if (!is_array($response)) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => 'The AI server returned an invalid response.',
    ]);
    exit;
}

if ($statusCode < 200 || $statusCode >= 300 || empty($response['success'])) {
    http_response_code($statusCode >= 400 ? $statusCode : 502);
    echo json_encode([
        'success' => false,
        'message' => $response['message'] ?? 'The AI request failed.',
        'errors' => $response['errors'] ?? [],
    ]);
    exit;
}

$baseUrl = 'http://127.0.0.1:5000';
$images = [];

foreach (($response['images'] ?? []) as $index => $path) {
    $path = (string) $path;
    $images[] = [
        'number' => $index + 1,
        'url' => str_starts_with($path, 'http')
            ? $path
            : $baseUrl . '/' . ltrim($path, '/'),
        'path' => $path,
    ];
}

if (count($images) !== 3) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => 'The AI server did not return three concepts.',
    ]);
    exit;
}

require_once '../../config/database.php';

$generationId = null;

try {
    $statement = $pdo->prepare(
        "INSERT INTO ai_nail_generations (
            customer_id,
            color_palette,
            design_style,
            nail_shape,
            nail_length,
            occasion,
            add_ons_json,
            instructions,
            prompt_text,
            image_1_url,
            image_2_url,
            image_3_url,
            provider,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'generated')"
    );

    $statement->execute([
        (int) $_SESSION['user_id'],
        $color,
        $style,
        $nailShape,
        $nailLength,
        $occasion,
        json_encode($pythonPayload['add_ons'], JSON_UNESCAPED_UNICODE),
        $instructions !== '' ? $instructions : null,
        $response['prompt'] ?? null,
        $images[0]['url'],
        $images[1]['url'],
        $images[2]['url'],
        $response['provider'] ?? 'demo',
    ]);

    $generationId = (int) $pdo->lastInsertId();
} catch (Throwable $exception) {
    // The generation still succeeds even if history storage is unavailable.
    $generationId = null;
}

echo json_encode([
    'success' => true,
    'message' => $response['message'] ?? 'Three nail concepts were created.',
    'generation_id' => $generationId,
    'prompt' => $response['prompt'] ?? '',
    'images' => $images,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
