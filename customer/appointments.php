<?php
declare(strict_types=1);

$pageTitle = 'My Appointments';
$customerPage = 'appointments';

require_once '../includes/customer-header.php';

$allowedFilters = ['all', 'upcoming', 'completed', 'cancelled'];
$filter = strtolower(trim($_GET['filter'] ?? 'all'));

if (!in_array($filter, $allowedFilters, true)) {
    $filter = 'all';
}

$where = '';
$params = [$customerId];

if ($filter === 'upcoming') {
    $where = "AND b.booking_date >= CURDATE()
              AND b.status IN ('pending', 'confirmed')";
} elseif ($filter === 'completed') {
    $where = "AND b.status = 'completed'";
} elseif ($filter === 'cancelled') {
    $where = "AND b.status IN ('cancelled', 'no_show')";
}

$bookings = [];

if (customerTableExists($pdo, 'bookings')) {
    $statement = $pdo->prepare(
        "SELECT
            b.*,
            s.name AS service_name,
            s.price AS service_price,
            st.fullname AS staff_name,
            p.status AS payment_status
         FROM bookings b
         LEFT JOIN services s ON s.id = b.service_id
         LEFT JOIN users st ON st.id = b.staff_id
         LEFT JOIN payments p ON p.booking_id = b.id
         WHERE b.customer_id = ?
         {$where}
         ORDER BY
            CASE WHEN b.booking_date >= CURDATE() THEN 0 ELSE 1 END,
            b.booking_date DESC,
            b.booking_time DESC"
    );
    $statement->execute($params);
    $bookings = $statement->fetchAll(PDO::FETCH_ASSOC);
}
?>

<main class="customer-page-shell">
    <header class="customer-page-heading">
        <div class="customer-page-heading-copy">
            <p class="customer-kicker">Your salon schedule</p>
            <h1>Appointments, without the back-and-forth.</h1>
            <p>
                Track confirmation, payment, assigned staff, and the services
                you have completed.
            </p>
        </div>

        <div class="customer-page-actions">
            <a class="customer-ui-button" href="services.php">Browse services</a>
            <a class="customer-ui-button primary" href="book.php">Book appointment ↗</a>
        </div>
    </header>

    <nav class="customer-filter-bar">
        <?php foreach ([
            'all' => 'All appointments',
            'upcoming' => 'Upcoming',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ] as $value => $label): ?>
            <a class="<?= $filter === $value ? 'active' : '' ?>" href="?filter=<?= $value ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <section class="customer-ui-panel">
        <?php if ($bookings): ?>
            <div class="customer-booking-list">
                <?php foreach ($bookings as $booking): ?>
                    <article class="customer-booking-row">
                        <div class="customer-booking-date">
                            <span><?= date('M', strtotime($booking['booking_date'])) ?></span>
                            <strong><?= date('d', strtotime($booking['booking_date'])) ?></strong>
                        </div>

                        <div class="customer-booking-main">
                            <span class="customer-status-pill <?= htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars(customerStatusLabel($booking['status']), ENT_QUOTES, 'UTF-8') ?>
                            </span>

                            <h3><?= htmlspecialchars($booking['service_name'] ?? 'Salon Appointment', ENT_QUOTES, 'UTF-8') ?></h3>
                            <p>
                                <?= date('l, F j, Y', strtotime($booking['booking_date'])) ?>
                                · <?= date('g:i A', strtotime($booking['booking_time'])) ?>
                            </p>
                        </div>

                        <div class="customer-booking-meta">
                            <p><strong>Professional:</strong> <?= htmlspecialchars($booking['staff_name'] ?? 'To be assigned', ENT_QUOTES, 'UTF-8') ?></p>
                            <p><strong>Payment:</strong> <?= htmlspecialchars(ucfirst($booking['payment_status'] ?? 'not submitted'), ENT_QUOTES, 'UTF-8') ?></p>
                            <p><strong>Estimated:</strong> <?= customerMoney($booking['quoted_price'] ?? $booking['service_price'] ?? 0) ?></p>
                        </div>

                        <a class="customer-ui-button" href="appointment-details.php?id=<?= (int) $booking['id'] ?>">
                            View details
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="customer-empty">
                <span>◇</span>
                <h3>No appointments in this view.</h3>
                <p>
                    Your appointments will appear here after you submit a booking request.
                </p>
                <a class="customer-ui-button primary" href="book.php">Book your first visit</a>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include '../includes/customer-footer.php'; ?>
