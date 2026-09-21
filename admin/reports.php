<?php
declare(strict_types=1);
require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/branch-context.php';
require_once '../includes/reporting-helper.php';

$pageTitle = 'Reports';
[$from, $to] = reportDateRange();
$bookingStatus = strtolower(trim((string) ($_GET['booking_status'] ?? 'all')));
if (!in_array($bookingStatus, ['all','pending','completed','cancelled'], true)) $bookingStatus = 'all';
$branchId = requireAssignedBranch($pdo);
$branchLabel = branchName($pdo, $branchId);
$report = buildSmartBookingReport($pdo, $from, $to, $branchId, $bookingStatus);
if (isset($_GET['export'])) {
    reportCsv((string) $_GET['export'], $report, $from, $to);
}
$reportContext = 'admin';
include '../includes/admin-header.php';
?>
<div class="admin-layout">
    <?php include '../includes/admin-sidebar.php'; ?>
    <main class="admin-content">
        <?php include '../includes/reports-dashboard-view.php'; ?>
        <?php include '../includes/admin-footer.php'; ?>
    </main>
</div>
