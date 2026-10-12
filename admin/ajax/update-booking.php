<?php
declare(strict_types=1);

require_once '../../includes/admin-auth.php';
require_once '../../config/database.php';
require_once '../../includes/premium-admin-helper.php';
require_once '../../includes/branch-context.php';
require_once '../../includes/booking-management-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    bookingManagementJson(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$input = $_POST;
if (str_contains((string) ($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json')) {
    $decoded = json_decode((string) file_get_contents('php://input'), true);
    if (is_array($decoded)) {
        $input = $decoded;
    }
}

if (!premiumAdminVerifyCsrf($input['csrf_token'] ?? null)) {
    bookingManagementJson(
        ['success' => false, 'message' => 'Your session expired. Refresh and try again.'],
        419
    );
}

$bookingId = filter_var($input['booking_id'] ?? null, FILTER_VALIDATE_INT);
$staffId = filter_var($input['staff_id'] ?? null, FILTER_VALIDATE_INT);
$requestedStatus = trim((string) ($input['status'] ?? ''));
$note = trim((string) ($input['note'] ?? ''));
$branchId = requireAssignedBranch($pdo);

if (!$bookingId) {
    bookingManagementJson(['success' => false, 'message' => 'Invalid booking.'], 422);
}

$booking = bookingManagementFetch($pdo, $bookingId, $branchId);
if (!$booking) {
    bookingManagementJson(['success' => false, 'message' => 'Booking not found.'], 404);
}

$currentStatus = (string) $booking['status'];
$newStatus = $requestedStatus !== '' ? $requestedStatus : $currentStatus;

if (!in_array($newStatus, bookingManagementAllowedStatuses(), true)) {
    bookingManagementJson(['success' => false, 'message' => 'Invalid booking status.'], 422);
}

if (
    $newStatus !== $currentStatus
    && !in_array($newStatus, bookingManagementTransitions($currentStatus), true)
) {
    bookingManagementJson(
        [
            'success' => false,
            'message' => sprintf(
                'A %s booking cannot move directly to %s.',
                bookingManagementStatusLabel($currentStatus),
                bookingManagementStatusLabel($newStatus)
            ),
        ],
        422
    );
}

if ($staffId) {
    $staff = $pdo->prepare(
        "SELECT COUNT(*) FROM users
         WHERE id = ? AND role = 'staff' AND status = 'active' AND branch_id = ?"
    );
    $staff->execute([$staffId, $branchId]);
    if (!(bool) $staff->fetchColumn()) {
        bookingManagementJson(
            ['success' => false, 'message' => 'Choose an active staff member from the salon.'],
            422
        );
    }

    if (
        bookingManagementHasConflict(
            $pdo,
            $bookingId,
            $branchId,
            $staffId,
            (string) $booking['booking_date'],
            (string) $booking['booking_time']
        )
    ) {
        bookingManagementJson(
            ['success' => false, 'message' => 'That staff member is already booked at this time.'],
            409
        );
    }
}

try {
    $pdo->beginTransaction();

    $sets = ['staff_id = ?', 'status = ?'];
    $values = [$staffId ?: null, $newStatus];
    if (bookingManagementColumnExists($pdo, 'bookings', 'updated_at')) {
        $sets[] = 'updated_at = CURRENT_TIMESTAMP';
    }

    $values[] = $bookingId;
    $values[] = $branchId;
    $statement = $pdo->prepare(
        "UPDATE bookings SET " . implode(', ', $sets) . "
         WHERE id = ? AND branch_id = ?"
    );
    $statement->execute($values);

    if ($newStatus !== $currentStatus) {
        bookingManagementRecordHistory(
            $pdo,
            $bookingId,
            $currentStatus,
            $newStatus,
            isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null,
            $note
        );
    }

    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    bookingManagementJson(
        ['success' => false, 'message' => 'The booking could not be updated.'],
        500
    );
}

$updated = bookingManagementFetch($pdo, $bookingId, $branchId);
$updated['reference'] = bookingManagementReference($updated);
$updated['status_label'] = bookingManagementStatusLabel((string) $updated['status']);

bookingManagementJson([
    'success' => true,
    'message' => 'Booking updated successfully.',
    'booking' => $updated,
]);
