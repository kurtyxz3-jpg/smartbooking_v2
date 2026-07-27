<?php
declare(strict_types=1);

require_once '../includes/staff-auth.php';
require_once '../config/database.php';
require_once '../includes/staff-helper.php';
require_once '../includes/branch-context.php';

$pageTitle = 'My Bookings';
$staffId = (int) $_SESSION['user_id'];
$branchId = requireAssignedBranch($pdo);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    $status = $_POST['status'] ?? '';

    $allowedStatuses = ['confirmed', 'completed'];

    if (!$bookingId || !in_array($status, $allowedStatuses, true)) {
        $error = 'Invalid booking update.';
    } else {
        $statement = $pdo->prepare(
            "UPDATE bookings
             SET status = ?
             WHERE id = ?
               AND staff_id = ?
               AND branch_id = ?"
        );
        $statement->execute([$status, $bookingId, $staffId, $branchId]);

        $message = 'Booking status updated successfully.';
    }
}

$filterStatus = $_GET['status'] ?? '';
$date = $_GET['date'] ?? '';

$sql = "SELECT *
        FROM bookings
        WHERE staff_id = ? AND branch_id = ?";
$params = [$staffId, $branchId];

if (in_array($filterStatus, ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'], true)) {
    $sql .= " AND status = ?";
    $params[] = $filterStatus;
}

if ($date !== '') {
    $sql .= " AND booking_date = ?";
    $params[] = $date;
}

$sql .= " ORDER BY booking_date DESC, booking_time DESC";

$statement = $pdo->prepare($sql);
$statement->execute($params);
$bookings = $statement->fetchAll();

include '../includes/staff-header.php';
?>

<div class="staff-layout">
    <?php include '../includes/staff-sidebar.php'; ?>

    <main class="staff-content">
        <div class="staff-page-header">
            <div>
                <h1>My Bookings</h1>
                <p>Review the appointments assigned to your staff account.</p>
            </div>
        </div>

        <?php if ($message !== ''): ?>
            <div class="staff-alert staff-alert-success">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="staff-alert staff-alert-danger">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <section class="staff-card">
            <form class="staff-filter" method="GET">
                <div class="staff-field">
                    <label for="date">Booking Date</label>
                    <input
                        id="date"
                        name="date"
                        type="date"
                        value="<?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="staff-field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All statuses</option>
                        <?php foreach (['pending', 'confirmed', 'completed', 'cancelled', 'no_show'] as $option): ?>
                            <option value="<?= $option ?>" <?= $filterStatus === $option ? 'selected' : '' ?>>
                                <?= ucfirst(str_replace('_', ' ', $option)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="staff-actions">
                    <button class="staff-btn staff-btn-primary" type="submit">
                        Filter
                    </button>

                    <a class="staff-btn staff-btn-light" href="bookings.php">
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <section class="staff-card staff-table-card">
            <div class="staff-card-heading">
                <div>
                    <h2>Assigned Appointments</h2>
                    <p><?= count($bookings) ?> booking record(s) found.</p>
                </div>
            </div>

            <div class="staff-table-wrap">
                <table class="staff-table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Update</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (!$bookings): ?>
                        <tr>
                            <td colspan="7" class="staff-empty">
                                No assigned bookings found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>#<?= (int) $booking['id'] ?></td>

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
                                    <span class="staff-badge <?= staffStatusClass((string) $booking['status']) ?>">
                                        <?= ucfirst(str_replace('_', ' ', (string) $booking['status'])) ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="staff-booking-actions">
                                        <?php if ($booking['status'] === 'pending'): ?>
                                            <form method="POST">
                                                <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                                <input type="hidden" name="status" value="confirmed">
                                                <button class="staff-action-btn staff-action-confirm" type="submit">
                                                    Confirm
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if (in_array($booking['status'], ['pending', 'confirmed'], true)): ?>
                                            <form method="POST">
                                                <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                                <input type="hidden" name="status" value="completed">
                                                <button class="staff-action-btn staff-action-complete" type="submit">
                                                    Complete
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if (in_array($booking['status'], ['completed', 'cancelled', 'no_show'], true)): ?>
                                            <span class="staff-action-muted">No action</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php include '../includes/staff-footer.php'; ?>
    </main>
</div>
