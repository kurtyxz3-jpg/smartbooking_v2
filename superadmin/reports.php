<?php
declare(strict_types=1);
require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
require_once '../includes/branch-context.php';
require_once '../includes/reporting-helper.php';

$pageTitle = 'Business Reports';
[$from, $to] = reportDateRange();
$branchId = selectedSuperAdminBranch($pdo);
$branchLabel = branchName($pdo, $branchId);
$report = buildSmartBookingReport($pdo, $from, $to, $branchId);
if (isset($_GET['export'])) {
    reportCsv((string) $_GET['export'], $report, $from, $to);
}
$reportContext = 'superadmin';
include '../includes/superadmin-header.php';
?>
<div class="dashboard">
    <?php include '../includes/superadmin-sidebar.php'; ?>
    <main class="content">
        <?php include '../includes/reports-dashboard-view.php'; ?>
        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>
