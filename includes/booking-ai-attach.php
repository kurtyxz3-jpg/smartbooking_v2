<?php
declare(strict_types=1);

require_once __DIR__ . '/ai-history-helper.php';

// Include this file immediately after a new booking is inserted.
// It accepts the common variable names used by SmartBooking booking handlers.
$resolvedBookingId = (int) ($bookingId ?? $newBookingId ?? 0);
$resolvedCustomerId = (int) (
    $customerId ?? $_SESSION['user_id'] ?? 0
);
if ($resolvedBookingId > 0 && $resolvedCustomerId > 0 && isset($pdo)) {
    aiAttachSelectedDesignToBooking(
        $pdo,
        $resolvedBookingId,
        $resolvedCustomerId
    );
}

