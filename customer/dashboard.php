<?php
declare(strict_types=1);

require_once '../includes/customer-auth.php';
require_once '../config/database.php';

$pageTitle = 'My Beauty Space';
$customerId = (int) $_SESSION['user_id'];

function customerTableExists(PDO $pdo, string $table): bool
{
    $statement = $pdo->prepare(
        "SELECT COUNT(*)
         FROM information_schema.tables
         WHERE table_schema = DATABASE()
           AND table_name = ?"
    );
    $statement->execute([$table]);

    return (bool) $statement->fetchColumn();
}

function customerCount(PDO $pdo, int $customerId, string $where = '1=1'): int
{
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

$upcomingCount = customerCount(
    $pdo,
    $customerId,
    "booking_date >= CURDATE()
     AND status IN ('pending', 'confirmed')"
);

$completedCount = customerCount(
    $pdo,
    $customerId,
    "status = 'completed'"
);

$nextAppointment = null;

if (customerTableExists($pdo, 'bookings')) {
    $statement = $pdo->prepare(
        "SELECT b.*, s.name AS service_name
         FROM bookings b
         LEFT JOIN services s ON s.id = b.service_id
         WHERE b.customer_id = ?
           AND b.booking_date >= CURDATE()
           AND b.status IN ('pending', 'confirmed')
         ORDER BY b.booking_date ASC, b.booking_time ASC
         LIMIT 1"
    );
    $statement->execute([$customerId]);
    $nextAppointment = $statement->fetch();
}

$firstName = trim((string) ($_SESSION['fullname'] ?? 'Guest'));
$firstName = explode(' ', $firstName)[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | NBW</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/customer-v2.css">
</head>

<body class="customer-v2-body">
<header class="customer-topbar">
    <a class="customer-brand" href="../index.php">
        <span>N</span>

        <div>
            <strong>Nails &amp; Body Worx</strong>
            <small>My Beauty Space</small>
        </div>
    </a>

    <nav class="customer-nav">
        <a class="active" href="dashboard.php">Home</a>
        <a href="book.php">Book</a>
        <a href="appointments.php">Appointments</a>
        <a href="ai-generator.php">AI Studio</a>
        <a href="payments.php">Payments</a>
    </nav>

    <div class="customer-user-menu">
        <a class="customer-profile-link" href="profile.php">
            <span><?= htmlspecialchars(strtoupper(substr($firstName, 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>

            <div>
                <strong><?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?></strong>
                <small>My profile</small>
            </div>
        </a>

        <a class="customer-logout" href="../auth/logout.php" aria-label="Logout">↗</a>
    </div>
</header>

<main class="customer-main">
    <section class="customer-welcome">
        <div class="customer-welcome-copy">
            <p class="customer-eyebrow">Your personal salon space</p>

            <h1>
                Hi, <?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>.
                What look are we creating next?
            </h1>

            <p>
                Explore new designs, manage upcoming appointments, and return to your
                favorite services without the usual booking back-and-forth.
            </p>

            <div class="customer-welcome-actions">
                <a class="customer-btn customer-btn-primary" href="book.php">
                    Book appointment
                    <span>↗</span>
                </a>

                <a class="customer-btn customer-btn-light" href="ai-generator.php">
                    Create nail design
                </a>
            </div>
        </div>

        <div class="customer-visual-card">
            <div class="customer-visual-glow"></div>

            <div class="customer-nail-preview">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <div class="customer-visual-label">
                <small>Suggested for you</small>
                <strong>Soft gold French</strong>
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
            <span>Designs per generation</span>
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

                        <h3>
                            <?= htmlspecialchars(
                                $nextAppointment['service_name'] ?? 'Salon Appointment',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </h3>

                        <span class="customer-status customer-status-<?= htmlspecialchars($nextAppointment['status'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= ucfirst($nextAppointment['status']) ?>
                        </span>
                    </div>

                    <a class="customer-round-link" href="appointments.php">↗</a>
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
            <div>
                <p>AI Nail Studio</p>
                <h2>Turn an idea into three design directions.</h2>
                <span>Choose color, shape, style, add-ons, and special instructions.</span>
            </div>

            <a href="ai-generator.php">
                Open studio
                <b>↗</b>
            </a>
        </article>

        <article class="customer-panel customer-promo-panel">
            <div class="customer-promo-badge">Limited offer</div>
            <strong>20% OFF</strong>
            <h2>Selected soft-opening services</h2>
            <p>Discover current salon promotions before confirming your visit.</p>
            <a href="services.php">Explore offers →</a>
        </article>

        <article class="customer-panel customer-quick-panel">
            <div class="customer-panel-heading">
                <div>
                    <p>Quick access</p>
                    <h2>Everything in one place</h2>
                </div>
            </div>

            <div class="customer-quick-links">
                <a href="appointments.php"><span>01</span><strong>My appointments</strong><b>↗</b></a>
                <a href="payments.php"><span>02</span><strong>Payments &amp; receipts</strong><b>↗</b></a>
                <a href="feedback.php"><span>03</span><strong>Share feedback</strong><b>↗</b></a>
                <a href="profile.php"><span>04</span><strong>Profile settings</strong><b>↗</b></a>
            </div>
        </article>
    </section>
</main>

<footer class="customer-footer">
    <p>&copy; <?= date('Y') ?> Nails &amp; Body Worx</p>
    <a href="../index.php">Visit public website ↗</a>
</footer>

<script src="../assets/js/customer-v2.js"></script>
</body>
</html>
