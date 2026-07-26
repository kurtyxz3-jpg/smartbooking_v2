<?php
declare(strict_types=1);
$resolvedBookingId = (int) ($bookingId ?? $newBookingId ?? 0);
$customerId = (int) ($_SESSION['user_id'] ?? 0);
$resultId = (int) ($_SESSION['selected_beauty_preview_id'] ?? 0);
if ($resolvedBookingId > 0 && $customerId > 0 && $resultId > 0 && isset($pdo)) {
    $find = $pdo->prepare(
        "SELECT COUNT(*) FROM ai_beauty_results
         WHERE id=? AND customer_id=? AND deleted_at IS NULL"
    );
    $find->execute([$resultId, $customerId]);
    if ((bool) $find->fetchColumn()) {
        $pdo->prepare(
            "UPDATE bookings SET selected_beauty_result_id=?
             WHERE id=? AND customer_id=?"
        )->execute([$resultId, $resolvedBookingId, $customerId]);
    }
    unset($_SESSION['selected_beauty_preview_id']);
}

