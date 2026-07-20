<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
require_once '../includes/superadmin-analytics-helper.php';

$pageTitle = 'Super Admin Dashboard';

$adminCount = saSafeCount($pdo, 'users', "role = 'admin'");
$staffCount = saSafeCount($pdo, 'users', "role = 'staff'");
$customerCount = saSafeCount($pdo, 'users', "role = 'customer'");
$todayBookings = saSafeCount($pdo, 'bookings', 'booking_date = CURDATE()');
$pendingPayments = saSafeCount($pdo, 'payments', "status = 'pending'");
$lowStockCount = saSafeCount(
    $pdo,
    'inventory',
    "status IN ('low_stock', 'out_of_stock')"
);

$totalRevenue = 0.0;

if (saTableExists($pdo, 'payments') && saColumnExists($pdo, 'payments', 'amount')) {
    $totalRevenue = saSafeSum(
        $pdo,
        'payments',
        'amount',
        "status = 'verified'"
    );
} elseif (
    saTableExists($pdo, 'bookings') &&
    saTableExists($pdo, 'services') &&
    saColumnExists($pdo, 'bookings', 'service_id') &&
    saColumnExists($pdo, 'services', 'price')
) {
    $statement = $pdo->query(
        "SELECT COALESCE(SUM(s.price), 0)
         FROM bookings b
         JOIN services s ON s.id = b.service_id
         WHERE b.status = 'completed'"
    );

    $totalRevenue = (float) $statement->fetchColumn();
}

$recentActivities = [];

if (saTableExists($pdo, 'audit_logs')) {
    $recentActivities = $pdo->query(
        "SELECT username, action, module, description, created_at
         FROM audit_logs
         ORDER BY created_at DESC
         LIMIT 6"
    )->fetchAll();
}

include '../includes/superadmin-header.php';
?>

<div class="dashboard">
    <?php include '../includes/superadmin-sidebar.php'; ?>

    <main class="content">
        <div class="page-header analytics-page-header">
            <div>
                <h1 class="gold-title">Super Admin Dashboard</h1>

                <p>
                    Welcome,
                    <?= htmlspecialchars(
                        $_SESSION['fullname'] ?? 'Super Administrator',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>.
                    Review system-wide operations and business performance.
                </p>
            </div>

            <div class="analytics-header-actions">
                <a class="btn btn-light" href="reports.php">Reports</a>
                <a class="btn btn-gold" href="sales-analytics.php">Sales Analytics</a>
            </div>
        </div>

        <section class="executive-kpis">
            <article class="executive-kpi executive-kpi-revenue">
                <small>Total Revenue</small>
                <h2><?= saMoney($totalRevenue) ?></h2>
                <p>Verified payments or completed service value</p>
            </article>

            <article class="executive-kpi">
                <small>Today's Bookings</small>
                <h2><?= $todayBookings ?></h2>
                <p>Appointments scheduled for today</p>
            </article>

            <article class="executive-kpi">
                <small>Active Staff</small>
                <h2><?= $staffCount ?></h2>
                <p>Registered staff accounts</p>
            </article>

            <article class="executive-kpi">
                <small>Customers</small>
                <h2><?= $customerCount ?></h2>
                <p>Registered customer accounts</p>
            </article>
        </section>

        <section class="executive-alerts">
            <article class="executive-alert-card alert-admin">
                <span>Administrators</span>
                <strong><?= $adminCount ?></strong>
                <small>Accounts managing daily operations</small>
            </article>

            <article class="executive-alert-card alert-payment">
                <span>Pending Payments</span>
                <strong><?= $pendingPayments ?></strong>
                <small>Payment records awaiting review</small>
            </article>

            <article class="executive-alert-card alert-stock">
                <span>Stock Alerts</span>
                <strong><?= $lowStockCount ?></strong>
                <small>Low or unavailable inventory items</small>
            </article>
        </section>

        <section class="executive-dashboard-grid">
            <article class="card">
                <div class="analytics-card-heading">
                    <div>
                        <h2>Recent Activity</h2>
                        <p>Latest system activity recorded in the audit logs.</p>
                    </div>

                    <a href="audit-logs.php">View audit logs</a>
                </div>

                <div class="activity-stream">
                    <?php if (!$recentActivities): ?>
                        <div class="analytics-empty">
                            No recent activity records are available.
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentActivities as $activity): ?>
                            <article class="activity-stream-item">
                                <div class="activity-stream-marker"></div>

                                <div>
                                    <strong>
                                        <?= htmlspecialchars(
                                            $activity['action'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </strong>

                                    <span>
                                        <?= htmlspecialchars(
                                            $activity['username'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                        &middot;
                                        <?= htmlspecialchars(
                                            $activity['module'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>

                                    <p>
                                        <?= htmlspecialchars(
                                            $activity['description'] ?? 'No additional description.',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </p>

                                    <small>
                                        <?= date(
                                            'M d, Y h:i A',
                                            strtotime($activity['created_at'])
                                        ) ?>
                                    </small>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </article>

            <article class="card">
                <div class="analytics-card-heading">
                    <div>
                        <h2>Management Shortcuts</h2>
                        <p>Open frequently used Super Admin modules.</p>
                    </div>
                </div>

                <div class="executive-quick-actions">
                    <a href="admins.php">
                        <strong>Administrators</strong>
                        <span>Manage administrator accounts.</span>
                    </a>

                    <a href="staff.php">
                        <strong>Staff Accounts</strong>
                        <span>Create and manage staff access.</span>
                    </a>

                    <a href="employment-records.php">
                        <strong>Employment Records</strong>
                        <span>Review hiring and compensation details.</span>
                    </a>

                    <a href="sales-analytics.php">
                        <strong>Sales Analytics</strong>
                        <span>Review revenue and performance trends.</span>
                    </a>

                    <a href="reports.php">
                        <strong>Business Reports</strong>
                        <span>Prepare printable operational summaries.</span>
                    </a>

                    <a href="backup.php">
                        <strong>Backup &amp; Restore</strong>
                        <span>Protect the SmartBooking database.</span>
                    </a>
                </div>
            </article>
        </section>

        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>
