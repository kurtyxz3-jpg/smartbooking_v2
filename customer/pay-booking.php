<?php
declare(strict_types=1);

$pageTitle = 'GCash Down Payment';
$customerPage = 'payments';

require_once '../includes/customer-header.php';

$bookingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
    ?: filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);

if (!$bookingId) {
    customerRedirectWithMessage('appointments.php', 'error', 'Booking not found.');
}

$fetchBooking = static function () use ($pdo, $customerId, $bookingId): ?array {
    $statement = $pdo->prepare(
        "SELECT
            b.*,
            s.name AS service_name,
            COALESCE(s.price, 0) AS service_price,
            br.branch_name,
            br.address AS branch_address,
            br.gcash_merchant_name,
            br.gcash_number,
            br.gcash_qr_path,
            p.id AS payment_id,
            p.status AS payment_status,
            p.reference_number,
            p.proof_path
         FROM bookings b
         LEFT JOIN services s ON s.id = b.service_id
         LEFT JOIN branches br ON br.id = b.branch_id
         LEFT JOIN payments p ON p.booking_id = b.id
         WHERE b.id = ?
           AND b.customer_id = ?
         ORDER BY p.id DESC
         LIMIT 1"
    );
    $statement->execute([$bookingId, $customerId]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
};

$booking = $fetchBooking();

if (!$booking) {
    customerRedirectWithMessage(
        'appointments.php',
        'error',
        'You cannot access that booking.'
    );
}

$isExpired = $booking['status'] === 'pending'
    && empty($booking['payment_id'])
    && !empty($booking['payment_due_at'])
    && strtotime((string) $booking['payment_due_at']) <= time();

if ($isExpired) {
    $pdo->prepare(
        "UPDATE bookings
         SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP
         WHERE id = ? AND customer_id = ? AND status = 'pending'"
    )->execute([$bookingId, $customerId]);

    customerRedirectWithMessage(
        'appointments.php',
        'error',
        'The 30-minute payment reservation expired. Please create a new booking.'
    );
}

$downPayment = round((float) $booking['service_price'] * 0.50, 2);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!customerVerifyCsrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session token expired. Refresh the page and try again.';
    }

    if (($booking['payment_status'] ?? '') === 'verified') {
        $errors[] = 'This booking already has a verified payment.';
    }

    if ($booking['status'] === 'cancelled') {
        $errors[] = 'A cancelled booking cannot receive a payment.';
    }

    $reference = strtoupper(trim((string) ($_POST['reference_number'] ?? '')));

    if (!preg_match('/^[A-Z0-9-]{8,30}$/', $reference)) {
        $errors[] = 'Enter the valid GCash transaction reference number.';
    }

    $proof = $_FILES['payment_proof'] ?? null;
    $proofPath = (string) ($booking['proof_path'] ?? '');

    if (!$proof || ($proof['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $errors[] = 'Upload the GCash receipt or transaction screenshot.';
    } elseif ((int) ($proof['size'] ?? 0) > 5 * 1024 * 1024) {
        $errors[] = 'The payment proof must be 5 MB or smaller.';
    } else {
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file((string) $proof['tmp_name']);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
        ];

        if (!isset($allowed[$mime])) {
            $errors[] = 'Upload a JPG, PNG, WEBP, or PDF payment proof.';
        } else {
            $directory = __DIR__ . '/../uploads/payment-proofs';

            if (!is_dir($directory) && !mkdir($directory, 0775, true)) {
                $errors[] = 'The payment proof folder could not be prepared.';
            } else {
                $filename = sprintf(
                    'booking-%d-%s.%s',
                    $bookingId,
                    bin2hex(random_bytes(8)),
                    $allowed[$mime]
                );

                if (!move_uploaded_file(
                    (string) $proof['tmp_name'],
                    $directory . '/' . $filename
                )) {
                    $errors[] = 'The payment proof could not be saved.';
                } else {
                    $proofPath = 'uploads/payment-proofs/' . $filename;
                }
            }
        }
    }

    if (!$errors) {
        $pdo->beginTransaction();

        try {
            $existing = $pdo->prepare(
                "SELECT id, status FROM payments
                 WHERE booking_id = ? ORDER BY id DESC LIMIT 1 FOR UPDATE"
            );
            $existing->execute([$bookingId]);
            $payment = $existing->fetch(PDO::FETCH_ASSOC);

            if ($payment && $payment['status'] === 'verified') {
                throw new RuntimeException('Payment was already verified.');
            }

            if ($payment) {
                $statement = $pdo->prepare(
                    "UPDATE payments
                     SET amount = ?,
                         payment_method = 'GCash',
                         reference_number = ?,
                         proof_path = ?,
                         status = 'pending',
                         submitted_at = CURRENT_TIMESTAMP,
                         verified_at = NULL,
                         verified_by = NULL,
                         review_note = NULL,
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id = ?"
                );
                $statement->execute([
                    $downPayment,
                    $reference,
                    $proofPath,
                    (int) $payment['id'],
                ]);
            } else {
                $statement = $pdo->prepare(
                    "INSERT INTO payments
                        (booking_id, amount, payment_method, reference_number,
                         proof_path, status, submitted_at)
                     VALUES (?, ?, 'GCash', ?, ?, 'pending', CURRENT_TIMESTAMP)"
                );
                $statement->execute([
                    $bookingId,
                    $downPayment,
                    $reference,
                    $proofPath,
                ]);
            }

            $pdo->prepare(
                "UPDATE bookings
                 SET payment_due_at = NULL,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = ? AND customer_id = ? AND status = 'pending'"
            )->execute([$bookingId, $customerId]);

            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'The payment submission could not be saved. Please try again.';
        }

        if (!$errors) {
            customerRedirectWithMessage(
                'appointment-details.php?id=' . $bookingId,
                'success',
                'GCash proof submitted. The salon will verify the transaction.'
            );
        }
    }
}

$deadline = !empty($booking['payment_due_at'])
    ? date('M j, Y g:i A', strtotime((string) $booking['payment_due_at']))
    : 'within 30 minutes';
$qrUrl = !empty($booking['gcash_qr_path'])
    ? '../' . ltrim((string) $booking['gcash_qr_path'], '/')
    : null;
?>

<main class="customer-page-shell">
    <header class="customer-page-heading">
        <div class="customer-page-heading-copy">
            <p class="customer-kicker">Secure your appointment</p>
            <h1>Pay the 50% reservation fee through GCash.</h1>
            <p>
                Send the exact amount to the official account of your selected
                salon, then submit the transaction reference and receipt.
            </p>
        </div>
        <div class="customer-page-actions">
            <a class="customer-ui-button" href="appointment-details.php?id=<?= (int) $bookingId ?>">
                Appointment details
            </a>
        </div>
    </header>

    <?php if ($errors): ?>
        <div class="gcash-alert error">
            <strong>Please review your payment:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="gcash-layout">
        <section class="customer-ui-panel gcash-instructions">
            <div class="customer-panel-title">
                <div>
                    <p class="customer-kicker">Official payment destination</p>
                    <h2><?= htmlspecialchars($booking['branch_name'] ?? 'Nails & Body Worx', ENT_QUOTES, 'UTF-8') ?></h2>
                </div>
                <span class="customer-status-pill pending">Awaiting payment</span>
            </div>

            <?php if ($qrUrl): ?>
                <div class="gcash-qr">
                    <img src="<?= htmlspecialchars($qrUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Official salon GCash QR code">
                </div>
            <?php else: ?>
                <div class="gcash-qr-missing">
                    <strong>QR code not yet uploaded</strong>
                    <p>Use the verified GCash account details shown below.</p>
                </div>
            <?php endif; ?>

            <dl class="customer-info-list">
                <div>
                    <dt>Merchant name</dt>
                    <dd><?= htmlspecialchars($booking['gcash_merchant_name'] ?: 'Contact the salon', ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
                <div>
                    <dt>GCash number</dt>
                    <dd><?= htmlspecialchars($booking['gcash_number'] ?: 'Not configured', ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
                <div>
                    <dt>Exact amount</dt>
                    <dd class="gcash-amount"><?= customerMoney($downPayment) ?></dd>
                </div>
                <div>
                    <dt>Reservation deadline</dt>
                    <dd><?= htmlspecialchars($deadline, ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
            </dl>

            <div class="gcash-reminder">
                Confirm that the recipient name is correct before sending.
                The booking is confirmed only after the salon verifies the payment.
            </div>
        </section>

        <section class="customer-ui-panel">
            <div class="customer-panel-title">
                <div>
                    <p class="customer-kicker">Verification details</p>
                    <h2>Submit your GCash receipt</h2>
                    <p>Never include your MPIN or one-time password.</p>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data" class="gcash-form">
                <input type="hidden" name="csrf_token" value="<?= customerCsrfToken() ?>">
                <input type="hidden" name="booking_id" value="<?= (int) $bookingId ?>">

                <div class="customer-form-group">
                    <label for="reference_number">Transaction reference number</label>
                    <input
                        id="reference_number"
                        name="reference_number"
                        maxlength="30"
                        value="<?= htmlspecialchars($_POST['reference_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Example: 1234567890123"
                        required
                    >
                </div>

                <div class="customer-form-group">
                    <label for="payment_proof">Receipt or transaction screenshot</label>
                    <input
                        id="payment_proof"
                        name="payment_proof"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp,.pdf"
                        required
                    >
                    <small>JPG, PNG, WEBP, or PDF. Maximum 5 MB.</small>
                </div>

                <div class="gcash-booking-summary">
                    <span><?= htmlspecialchars($booking['service_name'] ?? 'Salon service', ENT_QUOTES, 'UTF-8') ?></span>
                    <strong><?= customerMoney($downPayment) ?> due now</strong>
                </div>

                <button class="customer-ui-button primary gcash-submit" type="submit">
                    Submit payment for verification
                </button>
            </form>
        </section>
    </div>
</main>

<?php include '../includes/customer-footer.php'; ?>
