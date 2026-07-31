<?php
declare(strict_types=1);

require_once '../includes/staff-auth.php';
require_once '../config/database.php';
require_once '../includes/staff-helper.php';

$pageTitle = 'Staff Dashboard';
$staffId = (int) $_SESSION['user_id'];

$totalAssigned = staffSafeCount(
    $pdo,
    'bookings',
    'staff_id = ?',
    [$staffId]
);

$pendingAssigned = staffSafeCount(
    $pdo,
    'bookings',
    "staff_id = ? AND status = 'pending'",
    [$staffId]
);

$confirmedAssigned = staffSafeCount(
    $pdo,
    'bookings',
    "staff_id = ? AND status = 'confirmed'",
    [$staffId]
);

$completedAssigned = staffSafeCount(
    $pdo,
    'bookings',
    "staff_id = ? AND status = 'completed'",
    [$staffId]
);

$todayBookings = [];

if (staffTableExists($pdo, 'bookings')) {
    $statement = $pdo->prepare(
        "SELECT b.*, c.fullname AS customer_name, s.name AS service_name
         FROM bookings b
         LEFT JOIN users c ON c.id = b.customer_id
         LEFT JOIN services s ON s.id = b.service_id
         WHERE b.staff_id = ?
           AND b.booking_date = CURDATE()
         ORDER BY b.booking_time ASC"
    );
    $statement->execute([$staffId]);
    $todayBookings = $statement->fetchAll();
}

include '../includes/staff-header.php';
?>

<div class="staff-layout">
    <?php include '../includes/staff-sidebar.php'; ?>

    <main class="staff-content">
        <div class="staff-page-header">
            <div>
                <h1>Staff Dashboard</h1>

                <p>
                    Welcome,
                    <?= htmlspecialchars(
                        $_SESSION['fullname'] ?? 'Staff Member',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>.
                    Review your assigned services and daily schedule.
                </p>
            </div>

            <a class="staff-btn staff-btn-primary" href="bookings.php">
                View My Bookings
            </a>
        </div>

        <section class="staff-kpis">
            <article class="staff-kpi">
                <small>Total Assigned</small>
                <h2><?= $totalAssigned ?></h2>
                <p>All bookings assigned to you</p>
            </article>

            <article class="staff-kpi">
                <small>Pending</small>
                <h2><?= $pendingAssigned ?></h2>
                <p>Bookings awaiting confirmation</p>
            </article>

            <article class="staff-kpi">
                <small>Confirmed</small>
                <h2><?= $confirmedAssigned ?></h2>
                <p>Upcoming approved bookings</p>
            </article>

            <article class="staff-kpi">
                <small>Completed</small>
                <h2><?= $completedAssigned ?></h2>
                <p>Finished services</p>
            </article>
        </section>

        <section class="staff-dashboard-grid">
            <article class="staff-card">
                <div class="staff-card-heading">
                    <div>
                        <h2>Today's Schedule</h2>
                        <p>Your assigned appointments for today.</p>
                    </div>

                    <a href="schedule.php">View schedule</a>
                </div>

                <div class="staff-table-wrap">
                    <table class="staff-table staff-dashboard-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Customer</th>
                                <th>Service</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if (!$todayBookings): ?>
                            <tr>
                                <td colspan="4" class="staff-empty">
                                    No appointments are assigned to you today.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($todayBookings as $booking): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars(
                                            (string) ($booking['booking_time'] ?? '—'),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) ($booking['customer_name'] ?? '—'),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) ($booking['service_name'] ?? '—'),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <span class="staff-badge <?= staffStatusClass((string) ($booking['status'] ?? 'pending')) ?>">
                                            <?= ucfirst((string) ($booking['status'] ?? 'pending')) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="staff-card">
                <div class="staff-card-heading">
                    <div>
                        <h2>Quick Actions</h2>
                        <p>Access your most-used staff tools.</p>
                    </div>
                </div>

                <div class="staff-quick-actions">
                    <a href="bookings.php">
                        <strong>Assigned Bookings</strong>
                        <span>Review booking details and update service status.</span>
                    </a>

                    <a href="schedule.php">
                        <strong>My Schedule</strong>
                        <span>View appointments by date and time.</span>
                    </a>

                    <a href="performance.php">
                        <strong>Performance</strong>
                        <span>Check completed services and work summary.</span>
                    </a>

                    <a href="profile.php">
                        <strong>My Profile</strong>
                        <span>Review your account and employment information.</span>
                    </a>
                </div>
            </article>
        </section>

        <?php include '../includes/staff-footer.php'; ?>
    </main>
</div>
