<?php
declare(strict_types=1);

$pageTitle = 'Payments';
$customerPage = 'payments';

require_once '../includes/customer-header.php';

$payments = [];

if (customerTableExists($pdo, 'payments')) {
    $statement = $pdo->prepare(
        "SELECT
            p.*,
            b.booking_date,
            b.booking_time,
            s.name AS service_name
         FROM payments p
         INNER JOIN bookings b ON b.id = p.booking_id
         LEFT JOIN services s ON s.id = b.service_id
         WHERE b.customer_id = ?
         ORDER BY p.created_at DESC"
    );
    $statement->execute([$customerId]);
    $payments = $statement->fetchAll(PDO::FETCH_ASSOC);
}
?>

<main class="customer-page-shell">
    <header class="customer-page-heading">
        <div class="customer-page-heading-copy">
            <p class="customer-kicker">Payment records</p>
            <h1>Clear payment status for every visit.</h1>
            <p>
                Review submitted amounts, verification results, references,
                and their connected appointments.
            </p>
        </div>

        <div class="customer-page-actions">
            <a class="customer-ui-button" href="appointments.php">View appointments</a>
        </div>
    </header>

    <section class="customer-ui-panel">
        <?php if ($payments): ?>
            <?php foreach ($payments as $payment): ?>
                <article class="customer-payment-row">
                    <div>
                        <span class="customer-status-pill <?= $payment['status'] === 'verified' ? 'confirmed' : ($payment['status'] === 'rejected' ? 'cancelled' : 'pending') ?>">
                            <?= htmlspecialchars(ucfirst($payment['status']), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <h3><?= htmlspecialchars($payment['service_name'] ?? 'Salon Appointment', ENT_QUOTES, 'UTF-8') ?></h3>
                        <p>
                            <?= date('F j, Y', strtotime($payment['booking_date'])) ?>
                            · <?= htmlspecialchars($payment['payment_method'] ?: 'Method not specified', ENT_QUOTES, 'UTF-8') ?>
                            · Reference <?= htmlspecialchars($payment['reference_number'] ?: 'N/A', ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>

                    <div style="text-align:right">
                        <strong style="font-family:Georgia,serif;font-size:1.5rem"><?= customerMoney($payment['amount']) ?></strong>
                        <p><?= date('M j, Y', strtotime($payment['created_at'])) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="customer-empty">
                <span>₱</span>
                <h3>No payment records yet.</h3>
                <p>
                    Payment information will appear after you submit or the salon records a payment.
                </p>
                <a class="customer-ui-button primary" href="appointments.php">Review appointments</a>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include '../includes/customer-footer.php'; ?>
