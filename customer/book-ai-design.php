<?php
declare(strict_types=1);

require_once '../includes/customer-auth.php';
require_once '../config/database.php';
require_once '../includes/ai-history-helper.php';

$customerId = (int) $_SESSION['user_id'];
$designId = filter_input(INPUT_GET, 'design_id', FILTER_VALIDATE_INT) ?: 0;
$design = aiDesignForCustomer($pdo, $designId, $customerId);
if (!$design) {
    $_SESSION['flash_error'] = 'The selected AI design is unavailable.';
    header('Location: ai-designs.php');
    exit;
}

$pdo->beginTransaction();
try {
    $pdo->prepare(
        "UPDATE ai_generated_designs SET is_selected=0
         WHERE customer_id=? AND request_id=?"
    )->execute([$customerId, $design['request_id']]);
    $selectedAt = aiTableHasColumn($pdo, 'ai_generated_designs', 'selected_at')
        ? ', selected_at=NOW()' : '';
    $pdo->prepare(
        "UPDATE ai_generated_designs
         SET is_selected=1, is_saved=1 {$selectedAt}
         WHERE id=? AND customer_id=?"
    )->execute([$designId, $customerId]);
    $pdo->commit();
} catch (Throwable $exception) {
    $pdo->rollBack();
    $_SESSION['flash_error'] = 'The design could not be prepared for booking.';
    header('Location: ai-designs.php');
    exit;
}

$_SESSION['selected_ai_design_id'] = $designId;
$_SESSION['flash_success'] = 'AI design selected. Complete your appointment details.';

foreach (['booking.php', 'book.php', 'book-appointment.php', 'appointments.php'] as $page) {
    if (is_file(__DIR__ . DIRECTORY_SEPARATOR . $page)) {
        header('Location: ' . $page . '?ai_design_id=' . $designId);
        exit;
    }
}

header('Location: ai-designs.php?booking_page_missing=1');

