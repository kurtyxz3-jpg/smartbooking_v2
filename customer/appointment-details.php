<?php
declare(strict_types=1);

$pageTitle = 'Appointment Details';
$customerPage = 'appointment-details';

require_once '../includes/customer-header.php';

$bookingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
    ?: filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);

if (!$bookingId) {
    customerRedirectWithMessage(
        'appointments.php',
        'error',
        'Appointment not found.'
    );
}

$fetchBooking = static function () use ($pdo, $customerId, $bookingId): ?array {
    $statement = $pdo->prepare(
        "SELECT
            b.*,
            s.name AS service_name,
            s.description AS service_description,
            s.price AS service_price,
            s.duration AS service_duration,
            st.fullname AS staff_name,
            p.id AS payment_id,
            p.amount AS payment_amount,
            p.payment_method,
            p.reference_number,
            p.status AS payment_status
         FROM bookings b
         LEFT JOIN services s ON s.id = b.service_id
         LEFT JOIN users st ON st.id = b.staff_id
         LEFT JOIN payments p ON p.booking_id = b.id
         WHERE b.id = ?
           AND b.customer_id = ?
         LIMIT 1"
    );
    $statement->execute([$bookingId, $customerId]);

    $result = $statement->fetch(PDO::FETCH_ASSOC);

    return $result ?: null;
};

$booking = $fetchBooking();

if (!$booking) {
    customerRedirectWithMessage(
        'appointments.php',
        'error',
        'You cannot access that appointment.'
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!customerVerifyCsrf($_POST['csrf_token'] ?? null)) {
        customerRedirectWithMessage(
            'appointment-details.php?id=' . $bookingId,
            'error',
            'Your session token expired. Please try again.'
        );
    }

    $action = $_POST['action'] ?? '';

    if (!customerCanChangeBooking($booking)) {
        customerRedirectWithMessage(
            'appointment-details.php?id=' . $bookingId,
            'error',
            'This appointment can no longer be changed online.'
        );
    }

    if ($action === 'cancel') {
        $statement = $pdo->prepare(
            "UPDATE bookings
             SET status = 'cancelled',
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = ?
               AND customer_id = ?"
        );
        $statement->execute([$bookingId, $customerId]);

        customerRedirectWithMessage(
            'appointments.php?filter=cancelled',
            'success',
            'Your appointment was cancelled.'
        );
    }

    if ($action === 'reschedule') {
        $newDate = trim($_POST['booking_date'] ?? '');
        $newTime = trim($_POST['booking_time'] ?? '');

        $dateObject = DateTime::createFromFormat('Y-m-d', $newDate);
        $validDate = $dateObject
            && $dateObject->format('Y-m-d') === $newDate
            && $dateObject >= new DateTime('tomorrow');

        if (!$validDate || !preg_match('/^\d{2}:\d{2}$/', $newTime)) {
            customerRedirectWithMessage(
                'appointment-details.php?id=' . $bookingId,
                'error',
                'Choose a valid future date and time.'
            );
        }

        $conflictSql =
            "SELECT COUNT(*)
             FROM bookings
             WHERE id <> ?
               AND booking_date = ?
               AND booking_time = ?
               AND status IN ('pending', 'confirmed')";

        $conflictParams = [$bookingId, $newDate, $newTime . ':00'];

        if (!empty($booking['staff_id'])) {
            $conflictSql .= " AND staff_id = ?";
            $conflictParams[] = (int) $booking['staff_id'];
        }

        $conflict = $pdo->prepare($conflictSql);
        $conflict->execute($conflictParams);

        if ((int) $conflict->fetchColumn() > 0) {
            customerRedirectWithMessage(
                'appointment-details.php?id=' . $bookingId,
                'error',
                'That schedule is already reserved. Choose another time.'
            );
        }

        $statement = $pdo->prepare(
            "UPDATE bookings
             SET booking_date = ?,
                 booking_time = ?,
                 status = 'pending',
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = ?
               AND customer_id = ?"
        );
        $statement->execute([
            $newDate,
            $newTime . ':00',
            $bookingId,
            $customerId,
        ]);

        customerRedirectWithMessage(
            'appointment-details.php?id=' . $bookingId,
            'success',
            'Your new schedule was submitted for confirmation.'
        );
    }
}
?>

<main class="customer-page-shell">
    <div class="customer-page-actions" style="margin-bottom:15px">
        <a class="customer-ui-button" href="appointments.php">← Back to appointments</a>
    </div>

    <section class="customer-detail-hero">
        <div class="customer-detail-date">
            <span><?= date('M', strtotime($booking['booking_date'])) ?></span>
            <strong><?= date('d', strtotime($booking['booking_date'])) ?></strong>
        </div>

        <div class="customer-detail-copy">
            <span class="customer-status-pill <?= htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars(customerStatusLabel($booking['status']), ENT_QUOTES, 'UTF-8') ?>
            </span>

            <h1><?= htmlspecialchars($booking['service_name'] ?? 'Salon Appointment', ENT_QUOTES, 'UTF-8') ?></h1>
            <p>
                <?= date('l, F j, Y', strtotime($booking['booking_date'])) ?>
                · <?= date('g:i A', strtotime($booking['booking_time'])) ?>
            </p>
        </div>

        <a class="customer-ui-button primary" href="book.php?service=<?= urlencode($booking['service_name'] ?? '') ?>">
            Book again ↗
        </a>
    </section>

    <div class="customer-ui-grid" style="margin-top:15px">
        <section class="customer-ui-panel customer-span-7">
            <div class="customer-panel-title">
                <div>
                    <p class="customer-kicker">Visit information</p>
                    <h2>Appointment summary</h2>
                </div>
            </div>

            <dl class="customer-info-list">
                <div><dt>Service</dt><dd><?= htmlspecialchars($booking['service_name'] ?? 'Salon Appointment', ENT_QUOTES, 'UTF-8') ?></dd></div>
                <div><dt>Professional</dt><dd><?= htmlspecialchars($booking['staff_name'] ?? 'To be assigned', ENT_QUOTES, 'UTF-8') ?></dd></div>
                <div><dt>Estimated duration</dt><dd><?= (int) ($booking['estimated_duration'] ?? $booking['service_duration'] ?? 60) ?> minutes</dd></div>
                <div><dt>Estimated price</dt><dd><?= customerMoney($booking['quoted_price'] ?? $booking['service_price'] ?? 0) ?></dd></div>
                <div><dt>Booking code</dt><dd><?= htmlspecialchars($booking['booking_code'] ?? ('NBW-' . $booking['id']), ENT_QUOTES, 'UTF-8') ?></dd></div>
                <div><dt>Notes</dt><dd><?= nl2br(htmlspecialchars($booking['notes'] ?: 'No special instructions.', ENT_QUOTES, 'UTF-8')) ?></dd></div>
                <div><dt>AI reference</dt><dd><?= htmlspecialchars($booking['design_reference'] ?? 'None attached', ENT_QUOTES, 'UTF-8') ?></dd></div>
            </dl>
        </section>

        <aside class="customer-ui-panel customer-span-5">
            <div class="customer-panel-title">
                <div>
                    <p class="customer-kicker">Payment</p>
                    <h2>Payment status</h2>
                </div>
            </div>

            <?php if (!empty($booking['payment_id'])): ?>
                <dl class="customer-info-list">
                    <div><dt>Status</dt><dd><?= htmlspecialchars(ucfirst($booking['payment_status']), ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div><dt>Amount</dt><dd><?= customerMoney($booking['payment_amount']) ?></dd></div>
                    <div><dt>Method</dt><dd><?= htmlspecialchars($booking['payment_method'] ?: 'Not specified', ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div><dt>Reference</dt><dd><?= htmlspecialchars($booking['reference_number'] ?: 'None', ENT_QUOTES, 'UTF-8') ?></dd></div>
                </dl>
            <?php else: ?>
                <div class="customer-empty" style="min-height:220px">
                    <span>₱</span>
                    <h3>No payment submitted.</h3>
                    <p>Payment instructions will appear after the salon confirms your booking.</p>
                    <a class="customer-ui-button" href="payments.php">View payments</a>
                </div>
            <?php endif; ?>
        </aside>

        <?php if (customerCanChangeBooking($booking)): ?>
            <section class="customer-ui-panel customer-span-7">
                <div class="customer-panel-title">
                    <div>
                        <p class="customer-kicker">Change schedule</p>
                        <h2>Request a new date and time</h2>
                        <p>Changes are available until 24 hours before your appointment.</p>
                    </div>
                </div>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= customerCsrfToken() ?>">
                    <input type="hidden" name="booking_id" value="<?= (int) $bookingId ?>">
                    <input type="hidden" name="action" value="reschedule">

                    <div class="customer-form-grid">
                        <div class="customer-form-group">
                            <label for="booking_date">New date</label>
                            <input id="booking_date" name="booking_date" type="date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                        </div>

                        <div class="customer-form-group">
                            <label for="booking_time">New time</label>
                            <select id="booking_time" name="booking_time" required>
                                <option value="">Choose a time</option>
                                <?php for ($hour = 9; $hour <= 19; $hour++): ?>
                                    <?php foreach ([0, 30] as $minute): ?>
                                        <?php if ($hour === 19 && $minute === 30) continue; ?>
                                        <?php $time = sprintf('%02d:%02d', $hour, $minute); ?>
                                        <option value="<?= $time ?>"><?= date('g:i A', strtotime($time)) ?></option>
                                    <?php endforeach; ?>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="customer-form-actions">
                        <button class="customer-ui-button primary" type="submit">Submit reschedule request</button>
                    </div>
                </form>
            </section>

            <aside class="customer-ui-panel customer-span-5">
                <div class="customer-panel-title">
                    <div>
                        <p class="customer-kicker">Cancellation</p>
                        <h2>Cancel this visit</h2>
                    </div>
                </div>

                <p style="color:var(--nbw-text-muted);line-height:1.65">
                    Cancellation is permanent. You can book the same service again later.
                </p>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= customerCsrfToken() ?>">
                    <input type="hidden" name="booking_id" value="<?= (int) $bookingId ?>">
                    <input type="hidden" name="action" value="cancel">

                    <button
                        class="customer-ui-button danger"
                        type="submit"
                        data-customer-confirm="Cancel this appointment?"
                    >
                        Cancel appointment
                    </button>
                </form>
            </aside>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/customer-footer.php'; ?>
