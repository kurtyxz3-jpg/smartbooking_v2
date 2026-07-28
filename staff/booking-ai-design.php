<?php
declare(strict_types=1);
require_once '../includes/staff-auth.php';
require_once '../config/database.php';
require_once '../includes/branch-context.php';
$pageTitle = 'Appointment Design Reference';
$bookingId = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT) ?: 0;
$branchId = requireAssignedBranch($pdo);

if ($bookingId > 0) {
    $access = $pdo->prepare(
        "SELECT COUNT(*) FROM bookings
         WHERE id=? AND staff_id=? AND branch_id=?"
    );
    $access->execute([
        $bookingId,
        (int) $_SESSION['user_id'],
        $branchId,
    ]);
    if (!(bool) $access->fetchColumn()) {
        http_response_code(403);
        exit('You are not assigned to this appointment.');
    }
}
include '../includes/staff-header.php';
?>
<link rel="stylesheet" href="../assets/css/booking-ai-reference.css">
<div class="dashboard">
    <?php include '../includes/staff-sidebar.php'; ?>
    <main class="content">
        <div class="page-header">
            <div><h1 class="gold-title">Customer Design Reference</h1>
            <p>Use this concept as the visual reference for the appointment.</p></div>
            <a class="btn btn-light" href="appointments.php">Back to appointments</a>
        </div>
        <?php include '../includes/booking-ai-design-card.php'; ?>
        <?php include '../includes/staff-footer.php'; ?>
    </main>
</div>
