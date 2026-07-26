<?php
declare(strict_types=1);
require_once '../includes/admin-auth.php';
require_once '../config/database.php';
$pageTitle = 'Booking AI Design';
$bookingId = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT) ?: 0;
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

