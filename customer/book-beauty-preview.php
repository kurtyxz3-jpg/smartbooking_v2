<?php
declare(strict_types=1);
require_once '../includes/customer-auth.php';
require_once '../config/database.php';
$resultId = filter_input(INPUT_GET, 'result_id', FILTER_VALIDATE_INT) ?: 0;
$customerId = (int) $_SESSION['user_id'];
$find = $pdo->prepare(
    "SELECT id FROM ai_beauty_results
     WHERE id=? AND customer_id=? AND deleted_at IS NULL LIMIT 1"
);
$find->execute([$resultId, $customerId]);
if (!(bool) $find->fetchColumn()) {
    header('Location: beauty-previews.php');
    exit;
}
$_SESSION['selected_beauty_preview_id'] = $resultId;
$_SESSION['flash_success'] = 'Beauty preview selected. Complete your appointment details.';
foreach (['booking.php','book.php','book-appointment.php','appointments.php'] as $page) {
    if (is_file(__DIR__ . DIRECTORY_SEPARATOR . $page)) {
        header('Location: ' . $page . '?beauty_result_id=' . $resultId);
        exit;
    }
}
header('Location: beauty-previews.php');

