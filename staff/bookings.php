<?php
declare(strict_types=1);

require_once '../includes/staff-auth.php';
require_once '../config/database.php';
require_once '../includes/staff-helper.php';
require_once '../includes/branch-context.php';

require_once '../includes/booking-reliability.php';
require_once '../includes/booking-management-helper.php';
$_SESSION['staff_booking_csrf'] ??= bin2hex(random_bytes(32));
$pageTitle = 'My Bookings';
$staffId = (int) $_SESSION['user_id'];
$branchId = requireAssignedBranch($pdo);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals($_SESSION['staff_booking_csrf'],(string)($_POST['csrf_token']??''))) throw new DomainException('Your session expired. Refresh and try again.');
        $bookingId=(int)($_POST['booking_id']??0);$status=(string)($_POST['status']??'');
        bookingWriteBegin($pdo);
        $q=$pdo->prepare('SELECT * FROM bookings WHERE id=? AND staff_id=? AND branch_id=?');$q->execute([$bookingId,$staffId,$branchId]);$booking=$q->fetch();
        if (!$booking || !in_array($status,['confirmed','completed'],true)) throw new DomainException('Invalid booking update.');
        $allowed=$status==='confirmed' ? $booking['status']==='pending' : in_array($booking['status'],['confirmed','in_progress'],true);
        if (!$allowed) throw new DomainException('This appointment can no longer receive that status. Refresh the page.');
        if ($status==='confirmed') bookingChooseStaff($pdo,$staffId,$branchId,$booking['booking_date'],$booking['booking_time'],bookingSavedDuration($pdo,$booking),$bookingId);
        $pdo->prepare('UPDATE bookings SET status=?,updated_at=NOW() WHERE id=?')->execute([$status,$bookingId]);
        bookingManagementRecordHistory($pdo,$bookingId,$booking['status'],$status,$staffId,'Updated by assigned staff');
        $pdo->commit();$message='Booking status updated successfully.';
    } catch(Throwable $e) {
        if($pdo->inTransaction())$pdo->rollBack();
        $error=$e instanceof DomainException?$e->getMessage():'The booking could not be updated.';
    }
}

$filterStatus = $_GET['status'] ?? '';
$date = $_GET['date'] ?? '';

$sql = "SELECT b.*, c.fullname AS customer_name, s.name AS service_name
        FROM bookings b
        LEFT JOIN users c ON c.id = b.customer_id
        LEFT JOIN services s ON s.id = b.service_id
        WHERE b.staff_id = ? AND b.branch_id = ?";
$params = [$staffId, $branchId];

if (in_array($filterStatus, ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'], true)) {
    $sql .= " AND b.status = ?";
    $params[] = $filterStatus;
}

if ($date !== '') {
    $sql .= " AND b.booking_date = ?";
    $params[] = $date;
}

$sql .= " ORDER BY b.booking_date DESC, b.booking_time DESC";

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
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>AI Design</th>
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
                                        <?php if (!empty($booking['selected_ai_design_id'])): ?>
                                            <a class="staff-action-btn staff-action-confirm"
                                               href="booking-ai-design.php?booking_id=<?= (int) $booking['id'] ?>">
                                                View design
                                            </a>
                                        <?php else: ?>
                                            <span class="staff-action-muted">None</span>
                                        <?php endif; ?>
                                    </td>

                                <td>
                                    <div class="staff-booking-actions">
                                        <?php if ($booking['status'] === 'pending'): ?>
                                            <form method="POST">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['staff_booking_csrf'], ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                                <input type="hidden" name="status" value="confirmed">
                                                <button class="staff-action-btn staff-action-confirm" type="submit">
                                                    Confirm
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if (in_array($booking['status'], ['pending', 'confirmed'], true)): ?>
                                            <form method="POST">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['staff_booking_csrf'], ENT_QUOTES, 'UTF-8') ?>">
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
