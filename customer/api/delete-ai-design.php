<?php
declare(strict_types=1);

require_once '../../includes/customer-auth.php';
require_once '../../config/database.php';
require_once '../../includes/ai-history-helper.php';
header('Content-Type: application/json; charset=utf-8');

$payload = json_decode(file_get_contents('php://input') ?: '', true);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_array($payload)) {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}
if (!hash_equals(
    (string) ($_SESSION['ai_history_csrf'] ?? ''),
    (string) ($payload['csrf_token'] ?? '')
)) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Refresh the page and try again.']);
    exit;
}

$customerId = (int) $_SESSION['user_id'];
$designId = (int) ($payload['design_id'] ?? 0);
$design = aiDesignForCustomer($pdo, $designId, $customerId);
if (!$design) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Design was not found.']);
    exit;
}

if (aiTableHasColumn($pdo, 'ai_generated_designs', 'deleted_at')) {
    $delete = $pdo->prepare(
        "UPDATE ai_generated_designs
         SET is_saved=0, is_selected=0, deleted_at=NOW()
         WHERE id=? AND customer_id=?"
    );
} else {
    $delete = $pdo->prepare(
        "UPDATE ai_generated_designs SET is_saved=0, is_selected=0
         WHERE id=? AND customer_id=?"
    );
}
$delete->execute([$designId, $customerId]);
if ((int) ($_SESSION['selected_ai_design_id'] ?? 0) === $designId) {
    unset($_SESSION['selected_ai_design_id']);
}
aiLogUsage(
    $pdo, $customerId, 'deleted', (int) $design['request_id'],
    $designId, null, (string) $design['provider']
);
echo json_encode(['success' => true, 'message' => 'Design removed from your history.']);

