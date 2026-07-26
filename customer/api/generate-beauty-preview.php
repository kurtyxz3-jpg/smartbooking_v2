<?php
declare(strict_types=1);

require_once '../../includes/customer-auth.php';
require_once '../../config/database.php';
require_once '../../includes/ai-service.php';
header('Content-Type: application/json; charset=utf-8');

$payload = json_decode(file_get_contents('php://input') ?: '', true);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_array($payload)) {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}
if (!hash_equals(
    (string) ($_SESSION['beauty_ai_csrf'] ?? ''),
    (string) ($payload['csrf_token'] ?? '')
)) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Refresh the page and try again.']);
    exit;
}

$area = strtolower(trim((string) ($payload['area'] ?? '')));
$color = mb_substr(trim((string) ($payload['color'] ?? '')), 0, 100);
if (!in_array($area, ['lips', 'eyebrows'], true) || $color === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Complete the required preview fields.']);
    exit;
}
if (empty($payload['consent'])) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Photo-processing consent is required.']);
    exit;
}

$imageData = (string) ($payload['image_data'] ?? '');
if ($imageData !== '' && strlen($imageData) > 3_500_000) {
    http_response_code(413);
    echo json_encode(['success' => false, 'message' => 'The processed photo is too large.']);
    exit;
}
$settings = aiSettings($pdo);
$enabledKey = $area === 'lips' ? 'lip_preview_enabled' : 'eyebrow_preview_enabled';
if ($settings['ai_enabled'] !== '1' || ($settings[$enabledKey] ?? '1') !== '1') {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'This beauty preview is disabled.']);
    exit;
}

$customerId = (int) $_SESSION['user_id'];
$data = [
    'area' => $area,
    'color' => $color,
    'finish' => mb_substr(trim((string) ($payload['finish'] ?? '')), 0, 60),
    'service_style' => mb_substr(trim((string) ($payload['service_style'] ?? '')), 0, 100),
    'brow_shape' => mb_substr(trim((string) ($payload['brow_shape'] ?? '')), 0, 60),
    'thickness' => mb_substr(trim((string) ($payload['thickness'] ?? '')), 0, 40),
    'instructions' => mb_substr(trim((string) ($payload['instructions'] ?? '')), 0, 600),
    'image_data' => $imageData ?: null,
    'count' => 3,
];

$insert = $pdo->prepare(
    "INSERT INTO ai_beauty_requests
     (customer_id, beauty_area, color_value, service_style, finish_type,
      brow_shape, thickness, instructions, used_customer_photo, consent_given)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)"
);
$insert->execute([
    $customerId, $area, $color, $data['service_style'] ?: null,
    $data['finish'] ?: null, $data['brow_shape'] ?: null,
    $data['thickness'] ?: null, $data['instructions'] ?: null,
    $imageData !== '' ? 1 : 0,
]);
$requestId = (int) $pdo->lastInsertId();

try {
    $result = callAiService(
        $settings['ai_service_url'],
        '/api/v1/beauty-preview/generate',
        $data,
        (int) $settings['request_timeout_seconds']
    );
    $images = [];
    foreach (array_slice((array) ($result['images'] ?? []), 0, 3) as $index => $path) {
        $url = str_starts_with((string) $path, 'http')
            ? (string) $path
            : rtrim($settings['ai_service_url'], '/') . '/' . ltrim((string) $path, '/');
        $save = $pdo->prepare(
            "INSERT INTO ai_beauty_results
             (request_id, customer_id, result_number, image_path)
             VALUES (?, ?, ?, ?)"
        );
        $save->execute([$requestId, $customerId, $index + 1, $url]);
        $images[] = [
            'id' => (int) $pdo->lastInsertId(),
            'number' => $index + 1,
            'url' => $url,
        ];
    }
    if (count($images) !== 3) {
        throw new RuntimeException('The AI service did not return three previews.');
    }
    $pdo->prepare(
        "UPDATE ai_beauty_requests SET provider=?, model_name=?,
         status='completed', completed_at=NOW() WHERE id=?"
    )->execute([
        $result['provider'] ?? 'demo', $result['model'] ?? null, $requestId
    ]);
    echo json_encode([
        'success' => true,
        'message' => 'Three beauty previews were created.',
        'request_id' => $requestId,
        'images' => $images,
    ], JSON_UNESCAPED_SLASHES);
} catch (Throwable $exception) {
    $pdo->prepare(
        "UPDATE ai_beauty_requests SET status='failed', error_message=? WHERE id=?"
    )->execute([$exception->getMessage(), $requestId]);
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => $exception->getMessage()]);
}

