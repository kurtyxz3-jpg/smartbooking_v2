<?php
declare(strict_types=1);

require_once '../../includes/superadmin-auth.php';
require_once '../../config/database.php';
require_once '../../includes/ai-service.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$response = [
    'success' => false,
    'provider' => 'Unavailable',
    'model' => 'Unavailable',
    'configured' => false,
    'message' => 'Python AI service is offline.',
];

try {
    $settings = aiSettings($pdo);
    $health = callAiService(
        $settings['ai_service_url'],
        '/api/health',
        null,
        5
    );

    $response = [
        'success' => true,
        'provider' => (string) ($health['provider'] ?? 'Unknown'),
        'model' => (string) ($health['model'] ?? 'Not reported'),
        'configured' => !empty($health['configured']),
        'message' => (string) ($health['message'] ?? 'Connection successful.'),
    ];
} catch (Throwable $exception) {
    http_response_code(503);
    $response['message'] = $exception->getMessage();
}

echo json_encode(
    $response,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
