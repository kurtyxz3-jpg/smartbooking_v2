<?php
declare(strict_types=1);

require_once '../../includes/customer-auth.php';
require_once '../../config/database.php';
require_once '../../includes/ai-service.php';
header('Content-Type: application/json; charset=utf-8');

$settings = aiSettings($pdo);
if ($settings['ai_enabled'] !== '1' || $settings['nail_generator_enabled'] !== '1') {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'AI Nail Studio is disabled.']);
    exit;
}

try {
    echo json_encode(callAiService(
        $settings['ai_service_url'],
        '/api/health',
        null,
        5
    ));
} catch (Throwable $exception) {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => $exception->getMessage()]);
}
