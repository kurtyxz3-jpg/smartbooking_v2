<?php
declare(strict_types=1);

$pageTitle = 'My Beauty Space';
$customerPage = 'dashboard';

require_once '../includes/customer-header.php';

function customerDashboardCount(
    PDO $pdo,
    int $customerId,
    string $where
): int {
    if (!customerTableExists($pdo, 'bookings')) {
        return 0;
    }

    $statement = $pdo->prepare(
        "SELECT COUNT(*)
         FROM bookings
         WHERE customer_id = ?
           AND {$where}"
    );
    $statement->execute([$customerId]);

    return (int) $statement->fetchColumn();
}

$upcomingCount = customerDashboardCount(
    $pdo,
    $customerId,
    "booking_date >= CURDATE()
     AND status IN ('pending', 'confirmed')"
);

$completedCount = customerDashboardCount(
    $pdo,
    $customerId,
    "status = 'completed'"
);

$nextAppointment = null;

if (customerTableExists($pdo, 'bookings')) {
    $statement = $pdo->prepare(
        "SELECT
            b.*,
            s.name AS service_name,
            s.price AS service_price,
            st.fullname AS staff_name
         FROM bookings b
         LEFT JOIN services s ON s.id = b.service_id
         LEFT JOIN users st ON st.id = b.staff_id
         WHERE b.customer_id = ?
           AND b.booking_date >= CURDATE()
           AND b.status IN ('pending', 'confirmed')
         ORDER BY b.booking_date ASC, b.booking_time ASC
         LIMIT 1"
    );
    $statement->execute([$customerId]);
    $nextAppointment = $statement->fetch(PDO::FETCH_ASSOC);
}
?>

<main class="customer-main">
    <section class="customer-welcome">
        <div class="customer-welcome-copy">
            <p class="customer-eyebrow">Your personal salon space</p>

            <h1>
                Hi, <?= htmlspecialchars($customerFirstName, ENT_QUOTES, 'UTF-8') ?>.
                What look are we creating next?
            </h1>

            <p>
                Book services, review upcoming appointments, manage payments,
                and save ideas for your next Nails &amp; Body Worx visit.
            </p>

            <div class="customer-welcome-actions">
                <a class="customer-btn customer-btn-primary" href="book.php">
                    Book appointment <span>↗</span>
                </a>

                <a class="customer-btn customer-btn-light" href="services.php">
                    Browse services
                </a>
            </div>
        </div>

        <div class="customer-visual-card">
            <div class="customer-visual-glow"></div>
            <div class="customer-nail-preview">
                <span></span><span></span><span></span>
            </div>
            <div class="customer-visual-label">
                <small>AI Studio</small>
                <strong>Create three nail concepts</strong>
            </div>
        </div>
    </section>

    <section class="customer-stat-grid">
        <article>
            <small>Upcoming</small>
            <strong><?= $upcomingCount ?></strong>
            <span>Active appointments</span>
        </article>

        <article>
            <small>Completed</small>
            <strong><?= $completedCount ?></strong>
            <span>Salon visits</span>
        </article>

        <article>
            <small>AI Studio</small>
            <strong>3</strong>
            <span>Design ideas per request</span>
        </article>
    </section>

    <section class="customer-bento">
        <article class="customer-panel customer-next-panel">
            <div class="customer-panel-heading">
                <div>
                    <p>Next appointment</p>
                    <h2>Your upcoming visit</h2>
                </div>

                <a href="appointments.php">View all</a>
            </div>

            <?php if ($nextAppointment): ?>
                <div class="customer-appointment-card">
                    <div class="customer-date-block">
                        <span><?= date('M', strtotime($nextAppointment['booking_date'])) ?></span>
                        <strong><?= date('d', strtotime($nextAppointment['booking_date'])) ?></strong>
                    </div>

                    <div class="customer-appointment-info">
                        <small>
                            <?= date('l', strtotime($nextAppointment['booking_date'])) ?>
                            ·
                            <?= date('h:i A', strtotime($nextAppointment['booking_time'])) ?>
                        </small>

                        <h3><?= htmlspecialchars($nextAppointment['service_name'] ?? 'Salon Appointment', ENT_QUOTES, 'UTF-8') ?></h3>

                        <span class="customer-status customer-status-<?= htmlspecialchars($nextAppointment['status'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars(customerStatusLabel($nextAppointment['status']), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>

                    <a class="customer-round-link" href="appointment-details.php?id=<?= (int) $nextAppointment['id'] ?>">↗</a>
                </div>
            <?php else: ?>
                <div class="customer-empty-state">
                    <span>✦</span>
                    <h3>Your next look starts here.</h3>
                    <p>You do not have an upcoming appointment yet.</p>
                    <a href="book.php">Choose a service →</a>
                </div>
            <?php endif; ?>
        </article>

        <article class="customer-panel customer-ai-panel">
            <div class="customer-panel-heading">
                <div>
                    <p>Creative tools</p>
                    <h2>AI Nail Studio</h2>
                </div>
                <a href="beauty-studio.php?area=nails">Open studio</a>
            </div>

            <p>
                Combine your preferred color, style, add-ons, and instructions
                into three concepts that can be attached to a booking.
            </p>

            <a class="customer-btn customer-btn-primary" href="beauty-studio.php?area=nails">
                Start creating ↗
            </a>
        </article>

        <article class="customer-panel">
            <div class="customer-panel-heading">
                <div>
                    <p>Quick links</p>
                    <h2>Manage your visit</h2>
                </div>
            </div>

            <div class="customer-quick-links">
                <a href="appointments.php"><strong>Appointments</strong><span>View, reschedule, or cancel eligible visits.</span></a>
                <a href="payments.php"><strong>Payments</strong><span>Track payment records and verification.</span></a>
                <a href="feedback.php"><strong>Feedback</strong><span>Review completed salon experiences.</span></a>
                <a href="profile.php"><strong>Profile</strong><span>Update personal and display preferences.</span></a>
            </div>
        </article>
    </section>
</main>

<?php include '../includes/customer-footer.php'; ?>
