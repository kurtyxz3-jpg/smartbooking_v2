<?php
declare(strict_types=1);
require_once '../../includes/customer-auth.php';
require_once '../../config/database.php';
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
    echo json_encode(['success' => false, 'message' => 'Refresh and try again.']);
    exit;
}
$resultId = (int) ($payload['result_id'] ?? 0);
$customerId = (int) $_SESSION['user_id'];
$find = $pdo->prepare(
    "SELECT request_id FROM ai_beauty_results
     WHERE id=? AND customer_id=? AND deleted_at IS NULL LIMIT 1"
);
$find->execute([$resultId, $customerId]);
$requestId = (int) $find->fetchColumn();
if ($requestId < 1) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Preview was not found.']);
    exit;
}
$pdo->beginTransaction();
try {
    $pdo->prepare(
        "UPDATE ai_beauty_results SET is_selected=0
         WHERE request_id=? AND customer_id=?"
    )->execute([$requestId, $customerId]);
    $pdo->prepare(
        "UPDATE ai_beauty_results SET is_selected=1, is_saved=1,
         selected_at=NOW() WHERE id=? AND customer_id=?"
    )->execute([$resultId, $customerId]);
    $pdo->commit();
    $_SESSION['selected_beauty_preview_id'] = $resultId;
    echo json_encode(['success' => true, 'message' => 'Preview saved to your account.']);
} catch (Throwable $exception) {
    $pdo->rollBack();
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $exception->getMessage()]);
}

