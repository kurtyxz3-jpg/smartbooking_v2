<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/admin-helper.php';

$pageTitle = 'Admin Dashboard';

$today = date('Y-m-d');
$monthStart = date('Y-m-01');
$monthEnd = date('Y-m-t');

$bookingCount = adminSafeCount($pdo, 'bookings');
$serviceCount = adminSafeCount($pdo, 'services', "status = 'active'");
$staffCount = adminSafeCount($pdo, 'users', "role = 'staff' AND status = 'active'");
$lowStockCount = adminSafeCount(
    $pdo,
    'inventory',
    "status IN ('low_stock', 'out_of_stock')"
);

$todayBookings = adminSafeCount(
    $pdo,
    'bookings',
    "booking_date = CURDATE()
     AND status IN ('pending', 'confirmed')"
);

$pendingBookings = adminSafeCount(
    $pdo,
    'bookings',
    "status = 'pending'"
);

$confirmedBookings = adminSafeCount(
    $pdo,
    'bookings',
    "status = 'confirmed'"
);

$completedThisMonth = adminSafeCount(
    $pdo,
    'bookings',
    "status = 'completed'
     AND booking_date BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01')
     AND LAST_DAY(CURDATE())"
);

$verifiedRevenue = 0.0;
$monthlyRevenue = 0.0;

if (adminTableExists($pdo, 'payments')) {
    $verifiedRevenue = (float) $pdo->query(
        "SELECT COALESCE(SUM(amount), 0)
         FROM payments
         WHERE status = 'verified'"
    )->fetchColumn();

    $monthlyRevenue = (float) $pdo->query(
        "SELECT COALESCE(SUM(amount), 0)
         FROM payments
         WHERE status = 'verified'
           AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
           AND created_at < DATE_ADD(LAST_DAY(CURDATE()), INTERVAL 1 DAY)"
    )->fetchColumn();
}

$recentBookings = [];

if (adminTableExists($pdo, 'bookings')) {
    $recentBookings = $pdo->query(
        "SELECT
            b.id,
            b.booking_date,
            b.booking_time,
            b.status,
            c.fullname AS customer_name,
            s.name AS service_name,
            st.fullname AS staff_name,
            p.status AS payment_status
         FROM bookings b
         LEFT JOIN users c ON c.id = b.customer_id
         LEFT JOIN services s ON s.id = b.service_id
         LEFT JOIN users st ON st.id = b.staff_id
         LEFT JOIN payments p ON p.booking_id = b.id
         ORDER BY b.created_at DESC, b.id DESC
         LIMIT 7"
    )->fetchAll(PDO::FETCH_ASSOC);
}

$todaySchedule = [];

if (adminTableExists($pdo, 'bookings')) {
    $statement = $pdo->prepare(
        "SELECT
            b.id,
            b.booking_time,
            b.status,
            c.fullname AS customer_name,
            s.name AS service_name,
            st.fullname AS staff_name
         FROM bookings b
         LEFT JOIN users c ON c.id = b.customer_id
         LEFT JOIN services s ON s.id = b.service_id
         LEFT JOIN users st ON st.id = b.staff_id
         WHERE b.booking_date = CURDATE()
           AND b.status IN ('pending', 'confirmed')
         ORDER BY b.booking_time ASC"
    );
    $statement->execute();
    $todaySchedule = $statement->fetchAll(PDO::FETCH_ASSOC);
}

$stockAlerts = [];

if (adminTableExists($pdo, 'inventory')) {
    $stockAlerts = $pdo->query(
        "SELECT id, item_name, quantity, reorder_level, unit, status
         FROM inventory
         WHERE status IN ('low_stock', 'out_of_stock')
         ORDER BY
            CASE status
                WHEN 'out_of_stock' THEN 0
                ELSE 1
            END,
            quantity ASC,
            item_name ASC
         LIMIT 6"
    )->fetchAll(PDO::FETCH_ASSOC);
}

$trendLabels = [];
$bookingTrend = [];
$revenueTrend = [];

for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
    $date = date('Y-m-d', strtotime("-{$daysAgo} days"));
    $trendLabels[] = date('D', strtotime($date));
    $bookingTrend[$date] = 0;
    $revenueTrend[$date] = 0.0;
}

if (adminTableExists($pdo, 'bookings')) {
    $statement = $pdo->query(
        "SELECT booking_date, COUNT(*) AS total
         FROM bookings
         WHERE booking_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY)
             AND CURDATE()
         GROUP BY booking_date"
    );

    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $bookingTrend[$row['booking_date']] = (int) $row['total'];
    }
}

if (adminTableExists($pdo, 'payments')) {
    $statement = $pdo->query(
        "SELECT DATE(created_at) AS payment_date, SUM(amount) AS total
         FROM payments
         WHERE status = 'verified'
           AND DATE(created_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY)
               AND CURDATE()
         GROUP BY DATE(created_at)"
    );

    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $revenueTrend[$row['payment_date']] = (float) $row['total'];
    }
}

$bookingTrendValues = array_values($bookingTrend);
$revenueTrendValues = array_values($revenueTrend);
$maxBookingTrend = max(1, ...$bookingTrendValues);
$maxRevenueTrend = max(1, ...$revenueTrendValues);

$calendarYear = (int) date('Y');
$calendarMonth = (int) date('n');
$calendarFirstDay = new DateTime(sprintf('%04d-%02d-01', $calendarYear, $calendarMonth));
$calendarDays = (int) $calendarFirstDay->format('t');
$calendarStartWeekday = (int) $calendarFirstDay->format('N');

$calendarCounts = [];

if (adminTableExists($pdo, 'bookings')) {
    $statement = $pdo->prepare(
        "SELECT booking_date, COUNT(*) AS total
         FROM bookings
         WHERE booking_date BETWEEN ? AND ?
           AND status IN ('pending', 'confirmed', 'completed')
         GROUP BY booking_date"
    );
    $statement->execute([$monthStart, $monthEnd]);

    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $calendarCounts[$row['booking_date']] = (int) $row['total'];
    }
}

include '../includes/admin-header.php';
?>

<link rel="stylesheet" href="../assets/css/admin-dashboard-premium.css">

<div class="admin-layout">
    <?php include '../includes/admin-sidebar.php'; ?>

    <main class="admin-content">
        <header class="admin-page-header premium-dashboard-header">
            <div>
                <p class="premium-dashboard-kicker">Operations overview</p>
                <h1>Good day, <?= htmlspecialchars(
                    explode(' ', trim((string) ($_SESSION['fullname'] ?? 'Administrator')))[0],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>.</h1>

                <p>
                    Monitor today’s appointments, verified sales, staff activity,
                    and inventory attention points.
                </p>
            </div>

            <div class="premium-dashboard-actions">
                <a class="admin-btn admin-btn-light" href="reports.php">
                    Open Reports
                </a>

                <a class="admin-btn admin-btn-primary" href="bookings.php">
                    Manage Bookings
                </a>
            </div>
        </header>

        <section class="premium-dashboard-kpis">
            <article>
                <div class="premium-kpi-icon">◷</div>
                <div>
                    <small>Today’s appointments</small>
                    <strong><?= $todayBookings ?></strong>
                    <span><?= $pendingBookings ?> still pending review</span>
                </div>
            </article>

            <article>
                <div class="premium-kpi-icon">₱</div>
                <div>
                    <small>Verified revenue</small>
                    <strong>₱<?= number_format($monthlyRevenue, 0) ?></strong>
                    <span>₱<?= number_format($verifiedRevenue, 0) ?> lifetime verified</span>
                </div>
            </article>

            <article>
                <div class="premium-kpi-icon">✓</div>
                <div>
                    <small>Completed this month</small>
                    <strong><?= $completedThisMonth ?></strong>
                    <span><?= $confirmedBookings ?> confirmed upcoming</span>
                </div>
            </article>

            <article class="<?= $lowStockCount > 0 ? 'attention' : '' ?>">
                <div class="premium-kpi-icon">!</div>
                <div>
                    <small>Stock attention</small>
                    <strong><?= $lowStockCount ?></strong>
                    <span><?= $lowStockCount > 0 ? 'Items need replenishment' : 'Inventory levels are healthy' ?></span>
                </div>
            </article>
        </section>

        <section class="premium-dashboard-layout">
            <article class="admin-card premium-dashboard-main-chart">
                <div class="premium-card-heading">
                    <div>
                        <p class="premium-dashboard-kicker">Seven-day activity</p>
                        <h2>Bookings and verified revenue</h2>
                        <p>Daily activity from the previous six days through today.</p>
                    </div>

                    <div class="premium-chart-legend">
                        <span><i class="booking"></i> Bookings</span>
                        <span><i class="revenue"></i> Revenue</span>
                    </div>
                </div>

                <div class="premium-chart">
                    <?php foreach ($trendLabels as $index => $label): ?>
                        <?php
                        $bookingHeight =
                            ($bookingTrendValues[$index] / $maxBookingTrend) * 100;
                        $revenueHeight =
                            ($revenueTrendValues[$index] / $maxRevenueTrend) * 100;
                        ?>
                        <div class="premium-chart-column">
                            <div class="premium-chart-bars">
                                <span
                                    class="booking"
                                    style="height:<?= max(4, $bookingHeight) ?>%"
                                    title="<?= $bookingTrendValues[$index] ?> booking(s)"
                                ></span>

                                <span
                                    class="revenue"
                                    style="height:<?= max(4, $revenueHeight) ?>%"
                                    title="₱<?= number_format($revenueTrendValues[$index], 2) ?>"
                                ></span>
                            </div>

                            <small><?= $label ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="admin-card premium-dashboard-calendar">
                <div class="premium-card-heading">
                    <div>
                        <p class="premium-dashboard-kicker">Appointment calendar</p>
                        <h2><?= date('F Y') ?></h2>
                        <p>Select Bookings to manage a specific appointment.</p>
                    </div>

                    <a href="bookings.php">Full schedule →</a>
                </div>

                <div class="premium-calendar-weekdays">
                    <?php foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday): ?>
                        <span><?= $weekday ?></span>
                    <?php endforeach; ?>
                </div>

                <div class="premium-calendar-grid">
                    <?php for ($blank = 1; $blank < $calendarStartWeekday; $blank++): ?>
                        <span class="blank"></span>
                    <?php endfor; ?>

                    <?php for ($day = 1; $day <= $calendarDays; $day++): ?>
                        <?php
                        $dateKey = sprintf(
                            '%04d-%02d-%02d',
                            $calendarYear,
                            $calendarMonth,
                            $day
                        );
                        $count = $calendarCounts[$dateKey] ?? 0;
                        ?>
                        <a
                            class="<?= $dateKey === $today ? 'today' : '' ?> <?= $count > 0 ? 'has-bookings' : '' ?>"
                            href="bookings.php?date=<?= $dateKey ?>"
                        >
                            <strong><?= $day ?></strong>
                            <?php if ($count > 0): ?>
                                <small><?= $count ?></small>
                            <?php endif; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </article>

            <article class="admin-card premium-dashboard-recent">
                <div class="premium-card-heading">
                    <div>
                        <p class="premium-dashboard-kicker">Latest activity</p>
                        <h2>Recent bookings</h2>
                        <p>Customers, assigned professionals, and payment status.</p>
                    </div>

                    <a href="bookings.php">View all →</a>
                </div>

                <?php if ($recentBookings): ?>
                    <div class="premium-booking-list">
                        <?php foreach ($recentBookings as $booking): ?>
                            <a href="bookings.php?search=<?= (int) $booking['id'] ?>">
                                <div class="premium-booking-date">
                                    <span><?= date('M', strtotime($booking['booking_date'])) ?></span>
                                    <strong><?= date('d', strtotime($booking['booking_date'])) ?></strong>
                                </div>

                                <div class="premium-booking-copy">
                                    <div>
                                        <span class="premium-status <?= htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8') ?>">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>

                                        <small>#<?= (int) $booking['id'] ?></small>
                                    </div>

                                    <h3><?= htmlspecialchars(
                                        $booking['service_name'] ?? 'Salon appointment',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></h3>

                                    <p>
                                        <?= htmlspecialchars(
                                            $booking['customer_name'] ?? 'Guest customer',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                        ·
                                        <?= date('g:i A', strtotime($booking['booking_time'])) ?>
                                    </p>
                                </div>

                                <div class="premium-booking-meta">
                                    <strong><?= htmlspecialchars(
                                        $booking['staff_name'] ?? 'Unassigned',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></strong>

                                    <span>
                                        Payment:
                                        <?= htmlspecialchars(
                                            ucfirst($booking['payment_status'] ?? 'not submitted'),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>
                                </div>

                                <b>→</b>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="premium-dashboard-empty">
                        <span>◇</span>
                        <h3>No booking activity yet.</h3>
                        <p>New appointments will appear here.</p>
                    </div>
                <?php endif; ?>
            </article>

            <article class="admin-card premium-dashboard-today">
                <div class="premium-card-heading">
                    <div>
                        <p class="premium-dashboard-kicker">Today’s schedule</p>
                        <h2><?= date('l, F j') ?></h2>
                        <p><?= count($todaySchedule) ?> active appointment<?= count($todaySchedule) === 1 ? '' : 's' ?>.</p>
                    </div>
                </div>

                <?php if ($todaySchedule): ?>
                    <div class="premium-timeline">
                        <?php foreach ($todaySchedule as $appointment): ?>
                            <a href="bookings.php?search=<?= (int) $appointment['id'] ?>">
                                <time><?= date('g:i A', strtotime($appointment['booking_time'])) ?></time>

                                <div>
                                    <strong><?= htmlspecialchars(
                                        $appointment['service_name'] ?? 'Salon appointment',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></strong>

                                    <span>
                                        <?= htmlspecialchars(
                                            $appointment['customer_name'] ?? 'Guest customer',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                        ·
                                        <?= htmlspecialchars(
                                            $appointment['staff_name'] ?? 'Unassigned',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>
                                </div>

                                <i class="<?= htmlspecialchars($appointment['status'], ENT_QUOTES, 'UTF-8') ?>"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="premium-dashboard-empty compact">
                        <span>✓</span>
                        <h3>No active visits today.</h3>
                        <p>The schedule is clear.</p>
                    </div>
                <?php endif; ?>
            </article>

            <article class="admin-card premium-dashboard-stock">
                <div class="premium-card-heading">
                    <div>
                        <p class="premium-dashboard-kicker">Inventory attention</p>
                        <h2>Stock alerts</h2>
                        <p>Items at or below their reorder level.</p>
                    </div>

                    <a href="inventory.php">Inventory →</a>
                </div>

                <?php if ($stockAlerts): ?>
                    <div class="premium-stock-list">
                        <?php foreach ($stockAlerts as $item): ?>
                            <?php
                            $level =
                                (float) $item['reorder_level'] > 0
                                    ? min(
                                        100,
                                        ((float) $item['quantity']
                                            / (float) $item['reorder_level']) * 100
                                    )
                                    : 0;
                            ?>
                            <a href="inventory.php?search=<?= urlencode($item['item_name']) ?>">
                                <div>
                                    <strong><?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span>
                                        <?= number_format((float) $item['quantity'], 2) ?>
                                        <?= htmlspecialchars($item['unit'] ?? 'unit', ENT_QUOTES, 'UTF-8') ?>
                                        remaining
                                    </span>
                                </div>

                                <span class="premium-stock-status <?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= ucwords(str_replace('_', ' ', $item['status'])) ?>
                                </span>

                                <div class="premium-stock-meter">
                                    <i style="width:<?= $level ?>%"></i>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="premium-dashboard-empty compact">
                        <span>✓</span>
                        <h3>Stock levels look good.</h3>
                        <p>No items currently need attention.</p>
                    </div>
                <?php endif; ?>
            </article>

            <article class="admin-card premium-dashboard-quick">
                <div class="premium-card-heading">
                    <div>
                        <p class="premium-dashboard-kicker">Quick access</p>
                        <h2>Daily management</h2>
                        <p>Open the tools used most often by the salon administrator.</p>
                    </div>
                </div>

                <div class="premium-quick-grid">
                    <a href="bookings.php"><span>01</span><strong>Bookings</strong><small>Review and assign appointments.</small></a>
                    <a href="services.php"><span>02</span><strong>Services</strong><small>Manage the public catalogue.</small></a>
                    <a href="inventory.php"><span>03</span><strong>Inventory</strong><small>Track stock and reorder levels.</small></a>
                    <a href="staff.php"><span>04</span><strong>Staff</strong><small>View accounts and availability.</small></a>
                    <a href="payments.php"><span>05</span><strong>Payments</strong><small>Verify submitted payments.</small></a>
                    <a href="reports.php"><span>06</span><strong>Reports</strong><small>Review business performance.</small></a>
                </div>
            </article>
        </section>

        <?php include '../includes/admin-footer.php'; ?>
    </main>
</div>

<script src="../assets/js/admin-dashboard-premium.js"></script>
