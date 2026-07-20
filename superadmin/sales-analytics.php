<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
require_once '../includes/superadmin-analytics-helper.php';

$pageTitle = 'Sales Analytics';

[$startDate, $endDate] = saDateRange();

$revenue = 0.0;
$verifiedPayments = 0;
$completedBookings = 0;
$averageTransaction = 0.0;
$previousRevenue = 0.0;

$periodStart = new DateTime($startDate);
$periodEnd = new DateTime($endDate);
$periodDays = max(1, (int) $periodStart->diff($periodEnd)->format('%a') + 1);
$previousEnd = (clone $periodStart)->modify('-1 day');
$previousStart = (clone $previousEnd)->modify('-' . ($periodDays - 1) . ' days');

if (saTableExists($pdo, 'payments') && saColumnExists($pdo, 'payments', 'amount')) {
    $revenue = saSafeSum(
        $pdo,
        'payments',
        'amount',
        "status = 'verified' AND DATE(created_at) BETWEEN ? AND ?",
        [$startDate, $endDate]
    );

    $verifiedPayments = saSafeCount(
        $pdo,
        'payments',
        "status = 'verified' AND DATE(created_at) BETWEEN ? AND ?",
        [$startDate, $endDate]
    );

    $previousRevenue = saSafeSum(
        $pdo,
        'payments',
        'amount',
        "status = 'verified' AND DATE(created_at) BETWEEN ? AND ?",
        [$previousStart->format('Y-m-d'), $previousEnd->format('Y-m-d')]
    );
}

if (saTableExists($pdo, 'bookings')) {
    $completedBookings = saSafeCount(
        $pdo,
        'bookings',
        "status = 'completed' AND booking_date BETWEEN ? AND ?",
        [$startDate, $endDate]
    );
}

if ($verifiedPayments > 0) {
    $averageTransaction = $revenue / $verifiedPayments;
}

$growth = saPercentChange($revenue, $previousRevenue);

$dailySales = [];
$topServices = [];
$staffRevenue = [];

if (saTableExists($pdo, 'payments') && saColumnExists($pdo, 'payments', 'amount')) {
    $statement = $pdo->prepare(
        "SELECT DATE(created_at) AS sales_date,
                COALESCE(SUM(amount), 0) AS total
         FROM payments
         WHERE status = 'verified'
           AND DATE(created_at) BETWEEN ? AND ?
         GROUP BY DATE(created_at)
         ORDER BY sales_date ASC"
    );
    $statement->execute([$startDate, $endDate]);
    $dailySales = $statement->fetchAll();
}

if (
    saTableExists($pdo, 'bookings') &&
    saTableExists($pdo, 'services') &&
    saColumnExists($pdo, 'bookings', 'service_id') &&
    saColumnExists($pdo, 'services', 'name') &&
    saColumnExists($pdo, 'services', 'price')
) {
    $statement = $pdo->prepare(
        "SELECT
            s.name,
            COUNT(b.id) AS booking_count,
            COALESCE(SUM(s.price), 0) AS service_value
         FROM bookings b
         JOIN services s ON s.id = b.service_id
         WHERE b.status = 'completed'
           AND b.booking_date BETWEEN ? AND ?
         GROUP BY s.id, s.name
         ORDER BY service_value DESC, booking_count DESC
         LIMIT 8"
    );
    $statement->execute([$startDate, $endDate]);
    $topServices = $statement->fetchAll();
}

if (
    saTableExists($pdo, 'bookings') &&
    saTableExists($pdo, 'services') &&
    saTableExists($pdo, 'users') &&
    saColumnExists($pdo, 'bookings', 'staff_id') &&
    saColumnExists($pdo, 'services', 'price')
) {
    $statement = $pdo->prepare(
        "SELECT
            u.fullname,
            COUNT(b.id) AS completed_count,
            COALESCE(SUM(s.price), 0) AS service_value
         FROM bookings b
         JOIN services s ON s.id = b.service_id
         JOIN users u ON u.id = b.staff_id
         WHERE b.status = 'completed'
           AND b.booking_date BETWEEN ? AND ?
         GROUP BY u.id, u.fullname
         ORDER BY service_value DESC, completed_count DESC
         LIMIT 8"
    );
    $statement->execute([$startDate, $endDate]);
    $staffRevenue = $statement->fetchAll();
}

include '../includes/superadmin-header.php';
?>

<div class="dashboard">
    <?php include '../includes/superadmin-sidebar.php'; ?>

    <main class="content">
        <div class="page-header analytics-page-header">
            <div>
                <h1 class="gold-title">Sales Analytics</h1>
                <p>Review revenue, booking performance, top services, and staff contribution.</p>
            </div>

            <a class="btn btn-light" href="reports.php">Open Reports</a>
        </div>

        <section class="card analytics-filter-card">
            <form class="analytics-filter-form" method="GET">
                <div class="filter-field">
                    <label for="start_date">Start Date</label>
                    <input
                        id="start_date"
                        name="start_date"
                        type="date"
                        value="<?= htmlspecialchars($startDate, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="filter-field">
                    <label for="end_date">End Date</label>
                    <input
                        id="end_date"
                        name="end_date"
                        type="date"
                        value="<?= htmlspecialchars($endDate, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="analytics-filter-actions">
                    <button class="btn btn-gold" type="submit">Apply Range</button>
                    <a class="btn btn-light" href="sales-analytics.php">Current Month</a>
                </div>
            </form>
        </section>

        <section class="analytics-kpis">
            <article class="analytics-kpi analytics-kpi-featured">
                <small>Verified Revenue</small>
                <h2><?= saMoney($revenue) ?></h2>

                <?php if ($growth !== null): ?>
                    <span class="analytics-change <?= $growth >= 0 ? 'change-positive' : 'change-negative' ?>">
                        <?= $growth >= 0 ? '+' : '' ?><?= number_format($growth, 1) ?>%
                        vs previous period
                    </span>
                <?php else: ?>
                    <span class="analytics-change">No previous-period comparison</span>
                <?php endif; ?>
            </article>

            <article class="analytics-kpi">
                <small>Verified Payments</small>
                <h2><?= $verifiedPayments ?></h2>
                <p>Completed payment records</p>
            </article>

            <article class="analytics-kpi">
                <small>Completed Bookings</small>
                <h2><?= $completedBookings ?></h2>
                <p>Services completed in the selected period</p>
            </article>

            <article class="analytics-kpi">
                <small>Average Transaction</small>
                <h2><?= saMoney($averageTransaction) ?></h2>
                <p>Average verified payment value</p>
            </article>
        </section>

        <section class="analytics-main-grid">
            <article class="card">
                <div class="analytics-card-heading">
                    <div>
                        <h2>Daily Revenue</h2>
                        <p>Verified payment totals per day.</p>
                    </div>
                </div>

                <div class="analytics-bar-list">
                    <?php
                    $highestDailySale = 0.0;

                    foreach ($dailySales as $sale) {
                        $highestDailySale = max(
                            $highestDailySale,
                            (float) $sale['total']
                        );
                    }
                    ?>

                    <?php if (!$dailySales): ?>
                        <div class="analytics-empty">
                            No verified payment data is available for this date range.
                        </div>
                    <?php else: ?>
                        <?php foreach ($dailySales as $sale): ?>
                            <?php
                            $saleAmount = (float) $sale['total'];
                            $barWidth = $highestDailySale > 0
                                ? max(4, ($saleAmount / $highestDailySale) * 100)
                                : 4;
                            ?>

                            <div class="analytics-bar-item">
                                <div class="analytics-bar-label">
                                    <span>
                                        <?= date('M d', strtotime($sale['sales_date'])) ?>
                                    </span>

                                    <strong><?= saMoney($saleAmount) ?></strong>
                                </div>

                                <div class="analytics-bar-track">
                                    <div
                                        class="analytics-bar-fill"
                                        style="width: <?= number_format($barWidth, 2, '.', '') ?>%;"
                                    ></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </article>

            <article class="card">
                <div class="analytics-card-heading">
                    <div>
                        <h2>Period Summary</h2>
                        <p>Selected date range and business indicators.</p>
                    </div>
                </div>

                <div class="analytics-summary-list">
                    <div>
                        <span>Date Range</span>
                        <strong>
                            <?= date('M d, Y', strtotime($startDate)) ?>
                            —
                            <?= date('M d, Y', strtotime($endDate)) ?>
                        </strong>
                    </div>

                    <div>
                        <span>Period Length</span>
                        <strong><?= $periodDays ?> day(s)</strong>
                    </div>

                    <div>
                        <span>Previous Period</span>
                        <strong>
                            <?= $previousStart->format('M d, Y') ?>
                            —
                            <?= $previousEnd->format('M d, Y') ?>
                        </strong>
                    </div>

                    <div>
                        <span>Previous Revenue</span>
                        <strong><?= saMoney($previousRevenue) ?></strong>
                    </div>
                </div>
            </article>
        </section>

        <section class="analytics-comparison-grid">
            <article class="card">
                <div class="analytics-card-heading">
                    <div>
                        <h2>Top Services</h2>
                        <p>Highest-value completed services in the selected period.</p>
                    </div>
                </div>

                <div class="analytics-ranking">
                    <?php if (!$topServices): ?>
                        <div class="analytics-empty">
                            No completed service data is available.
                        </div>
                    <?php else: ?>
                        <?php foreach ($topServices as $index => $service): ?>
                            <div class="analytics-ranking-item">
                                <span class="analytics-rank"><?= $index + 1 ?></span>

                                <div>
                                    <strong>
                                        <?= htmlspecialchars(
                                            $service['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </strong>

                                    <small>
                                        <?= (int) $service['booking_count'] ?>
                                        completed booking(s)
                                    </small>
                                </div>

                                <b><?= saMoney((float) $service['service_value']) ?></b>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </article>

            <article class="card">
                <div class="analytics-card-heading">
                    <div>
                        <h2>Staff Contribution</h2>
                        <p>Completed service value attributed to staff members.</p>
                    </div>
                </div>

                <div class="analytics-ranking">
                    <?php if (!$staffRevenue): ?>
                        <div class="analytics-empty">
                            No staff revenue data is available.
                        </div>
                    <?php else: ?>
                        <?php foreach ($staffRevenue as $index => $staff): ?>
                            <div class="analytics-ranking-item">
                                <span class="analytics-rank"><?= $index + 1 ?></span>

                                <div>
                                    <strong>
                                        <?= htmlspecialchars(
                                            $staff['fullname'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </strong>

                                    <small>
                                        <?= (int) $staff['completed_count'] ?>
                                        completed service(s)
                                    </small>
                                </div>

                                <b><?= saMoney((float) $staff['service_value']) ?></b>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </article>
        </section>

        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>
