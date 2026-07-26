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
    (string) ($_SESSION['ai_csrf'] ?? ''),
    (string) ($payload['csrf_token'] ?? '')
)) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Refresh the page and try again.']);
    exit;
}

$designId = (int) ($payload['design_id'] ?? 0);
$customerId = (int) $_SESSION['user_id'];
$pdo->beginTransaction();
try {
    $find = $pdo->prepare(
        "SELECT request_id FROM ai_generated_designs
         WHERE id=? AND customer_id=? LIMIT 1"
    );
    $find->execute([$designId, $customerId]);
    $requestId = (int) $find->fetchColumn();
    if ($requestId < 1) {
        throw new RuntimeException('Generated design was not found.');
    }
    $clear = $pdo->prepare(
        "UPDATE ai_generated_designs SET is_selected=0
         WHERE request_id=? AND customer_id=?"
    );
    $clear->execute([$requestId, $customerId]);
    $selectedAt = aiTableHasColumn($pdo, 'ai_generated_designs', 'selected_at')
        ? ', selected_at=NOW()' : '';
    $select = $pdo->prepare(
        "UPDATE ai_generated_designs SET is_selected=1, is_saved=1 {$selectedAt}
         WHERE id=? AND customer_id=?"
    );
    $select->execute([$designId, $customerId]);
    $pdo->commit();
    $_SESSION['selected_ai_design_id'] = $designId;
    aiLogUsage($pdo, $customerId, 'selected', $requestId, $designId);
    echo json_encode([
        'success' => true,
        'message' => 'Design saved to your account.',
        'history_url' => 'ai-designs.php',
        'booking_url' => 'book-ai-design.php?design_id=' . $designId,
    ]);
} catch (Throwable $exception) {
    $pdo->rollBack();
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $exception->getMessage()]);
}
