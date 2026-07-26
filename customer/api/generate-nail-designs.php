<?php
declare(strict_types=1);

require_once '../../includes/customer-auth.php';
require_once '../../config/database.php';
require_once '../../includes/ai-service.php';
require_once '../../includes/ai-history-helper.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$payload = json_decode(file_get_contents('php://input') ?: '', true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
    exit;
}

if (!hash_equals(
    (string) ($_SESSION['ai_csrf'] ?? ''),
    (string) ($payload['csrf_token'] ?? '')
)) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Refresh the page and try again.']);
    exit;
}

$settings = aiSettings($pdo);
if ($settings['ai_enabled'] !== '1' || $settings['nail_generator_enabled'] !== '1') {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'AI Nail Studio is disabled.']);
    exit;
}

$data = [
    'color' => trim((string) ($payload['color'] ?? '')),
    'style' => trim((string) ($payload['style'] ?? '')),
    'nail_shape' => trim((string) ($payload['nail_shape'] ?? '')),
    'nail_length' => trim((string) ($payload['nail_length'] ?? '')),
    'occasion' => trim((string) ($payload['occasion'] ?? '')),
    'add_ons' => is_array($payload['add_ons'] ?? null)
        ? array_slice(array_map('strval', $payload['add_ons']), 0, 10)
        : [],
    'instructions' => mb_substr(trim((string) ($payload['instructions'] ?? '')), 0, 600),
    'count' => 3,
];

foreach (['color', 'style', 'nail_shape', 'nail_length', 'occasion'] as $field) {
    if ($data[$field] === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Complete all required design fields.']);
        exit;
    }
}

$customerId = (int) $_SESSION['user_id'];

try {
    $health = callAiService(
        $settings['ai_service_url'],
        '/api/health',
        null,
        10
    );
} catch (Throwable $exception) {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'message' => $exception->getMessage(),
    ]);
    exit;
}

$activeProvider = strtolower(trim((string) ($health['provider'] ?? 'unknown')));
$enforceDailyLimit = $activeProvider !== 'demo';

$resetAt = null;
try {
    $reset = $pdo->prepare(
        "SELECT MAX(reset_at) FROM ai_daily_limit_resets
         WHERE customer_id=? AND reset_at >= CURDATE()"
    );
    $reset->execute([$customerId]);
    $resetAt = $reset->fetchColumn() ?: null;
} catch (Throwable $exception) {
    $resetAt = null;
}
$limitStart = $resetAt ?: date('Y-m-d 00:00:00');
$limitStatement = $pdo->prepare(
    "SELECT COUNT(*) FROM ai_design_requests
     WHERE customer_id = ? AND created_at >= ?"
);
$limitStatement->execute([$customerId, $limitStart]);
if (
    $enforceDailyLimit
    && (int) $limitStatement->fetchColumn() >= (int) $settings['daily_customer_limit']
) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'You reached today’s AI generation limit.']);
    exit;
}

$insert = $pdo->prepare(
    "INSERT INTO ai_design_requests
        (customer_id, nail_shape, nail_length, colors, design_style,
         occasion, add_ons, instructions, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'processing')"
);
$insert->execute([
    $customerId, $data['nail_shape'], $data['nail_length'], $data['color'],
    $data['style'], $data['occasion'],
    json_encode($data['add_ons'], JSON_UNESCAPED_UNICODE),
    $data['instructions'] ?: null,
]);
$requestId = (int) $pdo->lastInsertId();

try {
    $result = callAiService(
        $settings['ai_service_url'],
        '/api/v1/nail-designs/generate',
        $data,
        (int) $settings['request_timeout_seconds']
    );
    $images = [];
    foreach (array_slice((array) ($result['images'] ?? []), 0, 3) as $index => $path) {
        $url = str_starts_with((string) $path, 'http')
            ? (string) $path
            : rtrim($settings['ai_service_url'], '/') . '/' . ltrim((string) $path, '/');
        $save = $pdo->prepare(
            "INSERT INTO ai_generated_designs
                (request_id, customer_id, design_number, image_path, is_saved)
             VALUES (?, ?, ?, ?, 1)"
        );
        $save->execute([$requestId, $customerId, $index + 1, $url]);
        $images[] = ['id' => (int) $pdo->lastInsertId(), 'number' => $index + 1, 'url' => $url];
    }
    if (count($images) !== 3) {
        throw new RuntimeException('The AI service did not return three designs.');
    }
    $done = $pdo->prepare(
        "UPDATE ai_design_requests SET provider=?, model_name=?, prompt_text=?,
         status='completed', completed_at=NOW() WHERE id=?"
    );
    $done->execute([
        $result['provider'] ?? 'unknown',
        $result['model'] ?? null,
        $result['prompt'] ?? null,
        $requestId,
    ]);
    aiLogUsage(
        $pdo, $customerId, 'generated', $requestId, null, null,
        (string) ($result['provider'] ?? 'unknown')
    );
    echo json_encode([
        'success' => true,
        'message' => $result['message'] ?? 'Three designs were created.',
        'request_id' => $requestId,
        'provider' => $result['provider'] ?? 'unknown',
        'images' => $images,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    $failed = $pdo->prepare(
        "UPDATE ai_design_requests SET status='failed', error_message=? WHERE id=?"
    );
    $failed->execute([$exception->getMessage(), $requestId]);
    aiLogUsage($pdo, $customerId, 'failed', $requestId);
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => $exception->getMessage()]);
}
