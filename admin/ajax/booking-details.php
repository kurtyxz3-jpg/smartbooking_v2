<?php
declare(strict_types=1);

require_once '../../includes/admin-auth.php';
require_once '../../config/database.php';
require_once '../../includes/branch-context.php';
require_once '../../includes/booking-management-helper.php';

$bookingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$branchId = requireAssignedBranch($pdo);

if (!$bookingId) {
    bookingManagementJson(['success' => false, 'message' => 'Invalid booking.'], 422);
}

$booking = bookingManagementFetch($pdo, $bookingId, $branchId);
if (!$booking) {
    bookingManagementJson(['success' => false, 'message' => 'Booking not found.'], 404);
}

$booking['reference'] = bookingManagementReference($booking);
$booking['status_label'] = bookingManagementStatusLabel((string) $booking['status']);
$booking['allowed_transitions'] = bookingManagementTransitions(
    (string) $booking['status']
);
$booking['history'] = bookingManagementHistory($pdo, $bookingId);

bookingManagementJson(['success' => true, 'booking' => $booking]);
