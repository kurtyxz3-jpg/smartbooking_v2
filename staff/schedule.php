<?php
declare(strict_types=1);

require_once '../includes/staff-auth.php';
require_once '../config/database.php';
require_once '../includes/staff-helper.php';
require_once '../includes/branch-context.php';
require_once '../includes/staff-schedule-helper.php';

$pageTitle = 'My Schedule';
$staffId = (int) $_SESSION['user_id'];
$branchId = requireAssignedBranch($pdo);
$weekStartInput = trim((string) ($_GET['week'] ?? ''));
$weekStart = $weekStartInput !== ''
    ? DateTimeImmutable::createFromFormat('!Y-m-d', $weekStartInput)
    : new DateTimeImmutable('monday this week');
if (!$weekStart) {
    $weekStart = new DateTimeImmutable('monday this week');
}
$weekEnd = $weekStart->modify('+6 days');

$weekly = staffScheduleWeekly($pdo, $staffId, $branchId);
$exceptions = staffScheduleExceptions(
    $pdo,
    $staffId,
    $branchId,
    $weekStart->format('Y-m-d'),
    $weekEnd->format('Y-m-d')
);
$exceptionsByDate = [];
foreach ($exceptions as $exception) {
    $exceptionsByDate[$exception['exception_date']][] = $exception;
}

$statement = $pdo->prepare(
    "SELECT b.*, c.fullname AS customer_name, s.name AS service_name,
            COALESCE(s.duration, 60) AS service_duration
     FROM bookings b
     LEFT JOIN users c ON c.id = b.customer_id
     LEFT JOIN services s ON s.id = b.service_id
     WHERE b.staff_id = ?
       AND b.branch_id = ?
       AND b.booking_date BETWEEN ? AND ?
       AND b.status NOT IN ('cancelled','no_show')
     ORDER BY b.booking_date, b.booking_time"
);
$statement->execute([
    $staffId,
    $branchId,
    $weekStart->format('Y-m-d'),
    $weekEnd->format('Y-m-d'),
]);
$bookings = $statement->fetchAll(PDO::FETCH_ASSOC);
$bookingsByDate = [];
foreach ($bookings as $booking) {
    $bookingsByDate[$booking['booking_date']][] = $booking;
}

include '../includes/staff-header.php';
?>
<link rel="stylesheet" href="../assets/css/staff-schedule-availability.css">

<div class="staff-layout">
    <?php include '../includes/staff-sidebar.php'; ?>
    <main class="staff-content staff-schedule-page">
        <header class="staff-page-header schedule-page-header">
            <div>
                <p class="schedule-kicker">Personal calendar</p>
                <h1>My Schedule</h1>
                <p>Review your weekly working hours, breaks, exceptions, and assigned appointments.</p>
            </div>
            <div class="week-navigation">
                <a href="?week=<?= $weekStart->modify('-7 days')->format('Y-m-d') ?>">← Previous</a>
                <strong><?= $weekStart->format('M j') ?> – <?= $weekEnd->format('M j, Y') ?></strong>
                <a href="?week=<?= $weekStart->modify('+7 days')->format('Y-m-d') ?>">Next →</a>
            </div>
        </header>

        <section class="staff-week-overview">
            <?php for ($offset = 0; $offset < 7; $offset++): ?>
                <?php
                $date = $weekStart->modify("+{$offset} days");
                $dateKey = $date->format('Y-m-d');
                $dayNumber = (int) $date->format('N');
                $rule = $weekly[$dayNumber];
                $dayExceptions = $exceptionsByDate[$dateKey] ?? [];
                $isFullDayOff = false;
                foreach ($dayExceptions as $exception) {
                    if ($exception['exception_type'] === 'day_off') {
                        $isFullDayOff = true;
                    }
                }
                ?>
                <article class="staff-day-column <?= (!(int)$rule['is_working'] || $isFullDayOff) ? 'is-off' : '' ?>">
                    <header>
                        <span><?= $date->format('D') ?></span>
                        <strong><?= $date->format('d') ?></strong>
                        <small><?= $date->format('M') ?></small>
                    </header>
                    <div class="staff-day-hours">
                        <?php if (!(int)$rule['is_working'] || $isFullDayOff): ?>
                            <span class="schedule-status-pill off">Day off</span>
                        <?php else: ?>
                            <strong><?= date('g:i A', strtotime($rule['start_time'])) ?> – <?= date('g:i A', strtotime($rule['end_time'])) ?></strong>
                            <?php if ($rule['break_start'] && $rule['break_end']): ?>
                                <small>Break <?= date('g:i A', strtotime($rule['break_start'])) ?> – <?= date('g:i A', strtotime($rule['break_end'])) ?></small>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php foreach ($dayExceptions as $exception): ?>
                        <?php if ($exception['exception_type'] !== 'day_off'): ?>
                            <div class="staff-exception-note">
                                <strong><?= ucwords(str_replace('_', ' ', $exception['exception_type'])) ?></strong>
                                <small><?= date('g:i A', strtotime($exception['start_time'])) ?> – <?= date('g:i A', strtotime($exception['end_time'])) ?></small>
                                <?php if ($exception['reason']): ?><span><?= htmlspecialchars($exception['reason']) ?></span><?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <div class="staff-day-bookings">
                        <?php if (empty($bookingsByDate[$dateKey])): ?><p>No appointments</p><?php endif; ?>
                        <?php foreach ($bookingsByDate[$dateKey] ?? [] as $booking): ?>
                            <a class="staff-schedule-booking" href="bookings.php?date=<?= $dateKey ?>">
                                <time><?= date('g:i A', strtotime($booking['booking_time'])) ?></time>
                                <strong><?= htmlspecialchars($booking['customer_name'] ?: 'Customer') ?></strong>
                                <span><?= htmlspecialchars($booking['service_name'] ?: 'Service') ?></span>
                                <small><?= ucwords(str_replace('_', ' ', $booking['status'])) ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endfor; ?>
        </section>

        <section class="schedule-card staff-schedule-legend">
            <div><span class="legend-dot work"></span><strong>Working hours</strong><small>Regular availability configured by the salon manager.</small></div>
            <div><span class="legend-dot break"></span><strong>Break</strong><small>Bookings are unavailable during this period.</small></div>
            <div><span class="legend-dot exception"></span><strong>Exception</strong><small>Temporary changes such as leave, training, or custom hours.</small></div>
        </section>
        <?php include '../includes/staff-footer.php'; ?>
    </main>
</div>
