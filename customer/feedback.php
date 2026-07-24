<?php
declare(strict_types=1);

$pageTitle = 'Feedback';
$customerPage = 'feedback';

require_once '../includes/customer-header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!customerVerifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Your session token expired.';
    } else {
        $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
        $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
        $comment = trim($_POST['comment'] ?? '');

        $eligible = $pdo->prepare(
            "SELECT COUNT(*)
             FROM bookings
             WHERE id = ?
               AND customer_id = ?
               AND status = 'completed'"
        );
        $eligible->execute([$bookingId, $customerId]);

        if ((int) $eligible->fetchColumn() === 0) {
            $error = 'Only your completed appointments can be reviewed.';
        } elseif (!$rating || $rating < 1 || $rating > 5) {
            $error = 'Choose a rating from 1 to 5.';
        } else {
            $statement = $pdo->prepare(
                "INSERT INTO feedback (
                    customer_id,
                    booking_id,
                    rating,
                    comment,
                    status
                ) VALUES (?, ?, ?, ?, 'new')
                ON DUPLICATE KEY UPDATE
                    rating = VALUES(rating),
                    comment = VALUES(comment),
                    status = 'new',
                    updated_at = CURRENT_TIMESTAMP"
            );

            try {
                $statement->execute([
                    $customerId,
                    $bookingId,
                    $rating,
                    $comment !== '' ? $comment : null,
                ]);

                customerRedirectWithMessage(
                    'feedback.php',
                    'success',
                    'Thank you. Your feedback was submitted.'
                );
            } catch (Throwable $exception) {
                $existing = $pdo->prepare(
                    "SELECT id FROM feedback
                     WHERE customer_id = ?
                       AND booking_id = ?
                     LIMIT 1"
                );
                $existing->execute([$customerId, $bookingId]);
                $feedbackId = $existing->fetchColumn();

                if ($feedbackId) {
                    $update = $pdo->prepare(
                        "UPDATE feedback
                         SET rating = ?,
                             comment = ?,
                             status = 'new'
                         WHERE id = ?"
                    );
                    $update->execute([
                        $rating,
                        $comment !== '' ? $comment : null,
                        $feedbackId,
                    ]);

                    customerRedirectWithMessage(
                        'feedback.php',
                        'success',
                        'Your feedback was updated.'
                    );
                }

                $error = 'Your feedback could not be saved.';
            }
        }
    }
}

$completed = $pdo->prepare(
    "SELECT
        b.id,
        b.booking_date,
        s.name AS service_name,
        f.rating,
        f.comment
     FROM bookings b
     LEFT JOIN services s ON s.id = b.service_id
     LEFT JOIN feedback f
        ON f.booking_id = b.id
       AND f.customer_id = b.customer_id
     WHERE b.customer_id = ?
       AND b.status = 'completed'
     ORDER BY b.booking_date DESC"
);
$completed->execute([$customerId]);
$completedBookings = $completed->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="customer-page-shell">
    <header class="customer-page-heading">
        <div class="customer-page-heading-copy">
            <p class="customer-kicker">Share your experience</p>
            <h1>Your feedback helps the salon improve.</h1>
            <p>
                Rate completed appointments and leave helpful comments for
                Nails &amp; Body Worx.
            </p>
        </div>
    </header>

    <?php if ($error !== ''): ?>
        <div class="customer-toast customer-toast-error" style="position:static;margin-bottom:15px">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <section class="customer-ui-panel">
        <?php if ($completedBookings): ?>
            <?php foreach ($completedBookings as $booking): ?>
                <article class="customer-feedback-row">
                    <div>
                        <h3><?= htmlspecialchars($booking['service_name'] ?? 'Salon Appointment', ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= date('F j, Y', strtotime($booking['booking_date'])) ?></p>

                        <?php if (!empty($booking['rating'])): ?>
                            <p style="margin-top:7px">
                                Your rating: <?= str_repeat('★', (int) $booking['rating']) ?>
                                <?= str_repeat('☆', 5 - (int) $booking['rating']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <button class="customer-ui-button" type="button" onclick="document.getElementById('feedback-<?= (int) $booking['id'] ?>').showModal()">
                        <?= !empty($booking['rating']) ? 'Edit review' : 'Leave feedback' ?>
                    </button>

                    <dialog id="feedback-<?= (int) $booking['id'] ?>" style="max-width:570px;width:calc(100% - 28px);border:0;border-radius:22px;padding:0">
                        <form method="POST" class="customer-ui-panel">
                            <input type="hidden" name="csrf_token" value="<?= customerCsrfToken() ?>">
                            <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                            <input type="hidden" id="rating" name="rating" value="<?= (int) ($booking['rating'] ?? 0) ?>">

                            <div class="customer-panel-title">
                                <div>
                                    <p class="customer-kicker">Review appointment</p>
                                    <h2><?= htmlspecialchars($booking['service_name'] ?? 'Salon Appointment', ENT_QUOTES, 'UTF-8') ?></h2>
                                </div>
                            </div>

                            <div data-rating-group style="display:flex;gap:7px;margin-bottom:15px">
                                <?php for ($rating = 1; $rating <= 5; $rating++): ?>
                                    <button
                                        class="customer-ui-button <?= $rating <= (int) ($booking['rating'] ?? 0) ? 'primary' : '' ?>"
                                        type="button"
                                        data-rating-value="<?= $rating ?>"
                                    >
                                        ★
                                    </button>
                                <?php endfor; ?>
                            </div>

                            <div class="customer-form-group">
                                <label for="comment-<?= (int) $booking['id'] ?>">Comment</label>
                                <textarea id="comment-<?= (int) $booking['id'] ?>" name="comment" placeholder="Tell the salon what went well or what could be improved."><?= htmlspecialchars($booking['comment'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>

                            <div class="customer-form-actions">
                                <button class="customer-ui-button primary" type="submit">Submit feedback</button>
                                <button class="customer-ui-button" type="button" onclick="this.closest('dialog').close()">Close</button>
                            </div>
                        </form>
                    </dialog>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="customer-empty">
                <span>★</span>
                <h3>No completed appointments to review.</h3>
                <p>
                    Feedback becomes available after the salon marks an appointment completed.
                </p>
                <a class="customer-ui-button primary" href="appointments.php">View appointments</a>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include '../includes/customer-footer.php'; ?>
