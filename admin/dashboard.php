<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/admin-helper.php';

$pageTitle = 'Admin Dashboard';

$bookingCount = adminSafeCount($pdo, 'bookings');
$serviceCount = adminSafeCount($pdo, 'services');
$inventoryCount = adminSafeCount($pdo, 'inventory');
$staffCount = adminSafeCount($pdo, 'users', "role = 'staff'");

$pendingBookings = adminSafeCount($pdo, 'bookings', "status = 'pending'");
$confirmedBookings = adminSafeCount($pdo, 'bookings', "status = 'confirmed'");
$completedBookings = adminSafeCount($pdo, 'bookings', "status = 'completed'");
$lowStockCount = adminSafeCount(
    $pdo,
    'inventory',
    "status IN ('low_stock', 'out_of_stock')"
);

$recentBookings = [];

if (adminTableExists($pdo, 'bookings')) {
    $recentBookings = $pdo->query(
        "SELECT *
         FROM bookings
         ORDER BY id DESC
         LIMIT 5"
    )->fetchAll();
}

include '../includes/admin-header.php';
?>

<div class="admin-layout">
    <?php include '../includes/admin-sidebar.php'; ?>

    <main class="admin-content">
        <div class="admin-page-header">
            <div>
                <h1>Admin Dashboard</h1>

                <p>
                    Welcome,
                    <?= htmlspecialchars(
                        $_SESSION['fullname'] ?? 'Administrator',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>.
                    Manage the daily operations of Nails &amp; Body Worx.
                </p>
            </div>

            <a class="admin-btn admin-btn-primary" href="bookings.php">
                View Bookings
            </a>
        </div>

        <section class="admin-kpis">
            <article class="admin-kpi">
                <small>Total Bookings</small>
                <h2><?= $bookingCount ?></h2>
                <p><?= $pendingBookings ?> pending</p>
            </article>

            <article class="admin-kpi">
                <small>Active Services</small>
                <h2><?= $serviceCount ?></h2>
                <p>Available salon services</p>
            </article>

            <article class="admin-kpi">
                <small>Inventory Items</small>
                <h2><?= $inventoryCount ?></h2>
                <p><?= $lowStockCount ?> require attention</p>
            </article>

            <article class="admin-kpi">
                <small>Staff Members</small>
                <h2><?= $staffCount ?></h2>
                <p>Registered staff accounts</p>
            </article>
        </section>

        <section class="admin-status-grid">
            <article class="admin-status-card status-pending">
                <span>Pending</span>
                <strong><?= $pendingBookings ?></strong>
                <small>Bookings awaiting review</small>
            </article>

            <article class="admin-status-card status-confirmed">
                <span>Confirmed</span>
                <strong><?= $confirmedBookings ?></strong>
                <small>Approved appointments</small>
            </article>

            <article class="admin-status-card status-completed">
                <span>Completed</span>
                <strong><?= $completedBookings ?></strong>
                <small>Finished salon services</small>
            </article>

            <article class="admin-status-card status-stock">
                <span>Stock Alerts</span>
                <strong><?= $lowStockCount ?></strong>
                <small>Low or out-of-stock items</small>
            </article>
        </section>

        <section class="admin-dashboard-grid">
            <article class="admin-card admin-recent-card">
                <div class="admin-card-heading">
                    <div>
                        <h2>Recent Bookings</h2>
                        <p>Latest appointment records in the system.</p>
                    </div>

                    <a href="bookings.php">View all</a>
                </div>

                <div class="admin-table-wrap">
                    <table class="admin-table admin-dashboard-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Service</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if (!$recentBookings): ?>
                            <tr>
                                <td colspan="6" class="admin-empty">
                                    No booking records found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentBookings as $booking): ?>
                                <?php
                                $status = strtolower((string) ($booking['status'] ?? 'pending'));

                                $statusClass = match ($status) {
                                    'confirmed' => 'admin-badge-success',
                                    'completed' => 'admin-badge-success',
                                    'cancelled', 'no_show' => 'admin-badge-danger',
                                    default => 'admin-badge-warning',
                                };
                                ?>

                                <tr>
                                    <td>#<?= (int) ($booking['id'] ?? 0) ?></td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) ($booking['customer_id'] ?? '—'),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) ($booking['service_id'] ?? '—'),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) ($booking['booking_date'] ?? '—'),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) ($booking['booking_time'] ?? '—'),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <span class="admin-badge <?= $statusClass ?>">
                                            <?= ucfirst($status) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="admin-card admin-quick-card">
                <div class="admin-card-heading">
                    <div>
                        <h2>Quick Actions</h2>
                        <p>Common daily management tasks.</p>
                    </div>
                </div>

                <div class="admin-quick-actions">
                    <a href="bookings.php">
                        <strong>Manage Bookings</strong>
                        <span>Review and update appointments.</span>
                    </a>

                    <a href="services.php">
                        <strong>Manage Services</strong>
                        <span>Update prices and availability.</span>
                    </a>

                    <a href="inventory.php">
                        <strong>Check Inventory</strong>
                        <span>Review stock levels and alerts.</span>
                    </a>

                    <a href="staff.php">
                        <strong>View Staff</strong>
                        <span>Access staff information.</span>
                    </a>

                    <a href="payments.php">
                        <strong>Verify Payments</strong>
                        <span>Review payment records.</span>
                    </a>

                    <a href="feedback.php">
                        <strong>Customer Feedback</strong>
                        <span>Review ratings and comments.</span>
                    </a>
                </div>
            </article>
        </section>

        <?php include '../includes/admin-footer.php'; ?>
    </main>
</div>
