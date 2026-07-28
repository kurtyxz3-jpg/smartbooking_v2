<?php
declare(strict_types=1);
require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/branch-context.php';
$pageTitle = 'Booking AI Design';
$bookingId = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT) ?: 0;
$branchId = requireAssignedBranch($pdo);
if ($bookingId > 0) {
    $access = $pdo->prepare(
        "SELECT COUNT(*) FROM bookings WHERE id=? AND branch_id=?"
    );
    $access->execute([$bookingId, $branchId]);
    if (!(bool) $access->fetchColumn()) {
        http_response_code(403);
        exit('This booking does not belong to your assigned branch.');
    }
}
include '../includes/admin-header.php';
?>
<link rel="stylesheet" href="../assets/css/booking-ai-reference.css">
<div class="dashboard">
    <?php include '../includes/admin-sidebar.php'; ?>
    <main class="content">
        <div class="page-header">
            <div><h1 class="gold-title">AI Design Reference</h1>
            <p>Review the nail concept selected for this booking.</p></div>
            <a class="btn btn-light" href="bookings.php">Back to bookings</a>
        </div>
        <?php include '../includes/booking-ai-design-card.php'; ?>
        <?php include '../includes/admin-footer.php'; ?>
    </main>
</div>
