<?php
declare(strict_types=1);

require_once '../includes/staff-auth.php';
require_once '../config/database.php';
require_once '../includes/staff-helper.php';
require_once '../includes/branch-context.php';

$pageTitle = 'My Schedule';
$staffId = (int) $_SESSION['user_id'];
$branchId = requireAssignedBranch($pdo);
$selectedDate = $_GET['date'] ?? date('Y-m-d');

$statement = $pdo->prepare(
    "SELECT *
     FROM bookings
     WHERE staff_id = ?
       AND branch_id = ?
       AND booking_date = ?
     ORDER BY booking_time ASC"
);
$statement->execute([$staffId, $branchId, $selectedDate]);
$schedule = $statement->fetchAll();

include '../includes/staff-header.php';
?>

<div class="staff-layout">
    <?php include '../includes/staff-sidebar.php'; ?>

    <main class="staff-content">
        <div class="staff-page-header">
            <div>
                <h1>My Schedule</h1>
                <p>View your assigned appointments for a selected date.</p>
            </div>
        </div>

        <section class="staff-card">
            <form class="staff-filter staff-schedule-filter" method="GET">
                <div class="staff-field">
                    <label for="date">Select Date</label>
                    <input
                        id="date"
                        name="date"
                        type="date"
                        value="<?= htmlspecialchars($selectedDate, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="staff-actions">
                    <button class="staff-btn staff-btn-primary" type="submit">
                        View Schedule
                    </button>
                </div>
            </form>
        </section>

        <section class="staff-card staff-schedule-card">
            <div class="staff-card-heading">
                <div>
                    <h2>
                        <?= date('F d, Y', strtotime($selectedDate)) ?>
                    </h2>
                    <p><?= count($schedule) ?> appointment(s) scheduled.</p>
                </div>
            </div>

            <div class="staff-timeline">
                <?php if (!$schedule): ?>
                    <div class="staff-empty">
                        No appointments are scheduled for this date.
                    </div>
                <?php else: ?>
                    <?php foreach ($schedule as $booking): ?>
                        <article class="staff-timeline-item">
                            <div class="staff-timeline-time">
                                <?= date('h:i A', strtotime((string) $booking['booking_time'])) ?>
                            </div>

                            <div class="staff-timeline-content">
                                <div>
                                    <strong>Booking #<?= (int) $booking['id'] ?></strong>
                                    <span>
                                        Customer <?= htmlspecialchars((string) ($booking['customer_id'] ?? '—')) ?>
                                        &middot;
                                        Service <?= htmlspecialchars((string) ($booking['service_id'] ?? '—')) ?>
                                    </span>
                                </div>

                                <span class="staff-badge <?= staffStatusClass((string) $booking['status']) ?>">
                                    <?= ucfirst(str_replace('_', ' ', (string) $booking['status'])) ?>
                                </span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <?php include '../includes/staff-footer.php'; ?>
    </main>
</div>
