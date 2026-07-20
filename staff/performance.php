<?php
declare(strict_types=1);

require_once '../includes/staff-auth.php';
require_once '../config/database.php';
require_once '../includes/staff-helper.php';

$pageTitle = 'Performance';
$staffId = (int) $_SESSION['user_id'];

$lifetimeCompleted = staffSafeCount(
    $pdo,
    'bookings',
    "staff_id = ? AND status = 'completed'",
    [$staffId]
);

$monthCompleted = 0;
$revenue = 0.00;

if (staffTableExists($pdo, 'bookings')) {
    $statement = $pdo->prepare(
        "SELECT COUNT(*)
         FROM bookings
         WHERE staff_id = ?
           AND status = 'completed'
           AND MONTH(booking_date) = MONTH(CURRENT_DATE())
           AND YEAR(booking_date) = YEAR(CURRENT_DATE())"
    );
    $statement->execute([$staffId]);
    $monthCompleted = (int) $statement->fetchColumn();
}

if (
    staffTableExists($pdo, 'bookings') &&
    staffTableExists($pdo, 'services') &&
    staffColumnExists($pdo, 'bookings', 'service_id') &&
    staffColumnExists($pdo, 'services', 'price')
) {
    $statement = $pdo->prepare(
        "SELECT COALESCE(SUM(s.price), 0)
         FROM bookings b
         JOIN services s ON b.service_id = s.id
         WHERE b.staff_id = ?
           AND b.status = 'completed'"
    );
    $statement->execute([$staffId]);
    $revenue = (float) $statement->fetchColumn();
}

include '../includes/staff-header.php';
?>

<div class="staff-layout">
    <?php include '../includes/staff-sidebar.php'; ?>

    <main class="staff-content">
        <div class="staff-page-header">
            <div>
                <h1>Performance</h1>
                <p>Review your completed services and contribution summary.</p>
            </div>
        </div>

        <section class="staff-kpis staff-performance-kpis">
            <article class="staff-kpi">
                <small>Lifetime Completed</small>
                <h2><?= $lifetimeCompleted ?></h2>
                <p>All completed services</p>
            </article>

            <article class="staff-kpi">
                <small>This Month</small>
                <h2><?= $monthCompleted ?></h2>
                <p>Completed services this month</p>
            </article>

            <article class="staff-kpi">
                <small>Service Revenue</small>
                <h2>₱<?= number_format($revenue, 2) ?></h2>
                <p>Value of completed assigned services</p>
            </article>
        </section>

        <section class="staff-card staff-performance-note">
            <h2>Performance Summary</h2>

            <p>
                These figures are based on bookings assigned to your staff account.
                Revenue is calculated using the prices stored in the Services module.
            </p>
        </section>

        <?php include '../includes/staff-footer.php'; ?>
    </main>
</div>
