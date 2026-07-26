<?php
declare(strict_types=1);

if (!isset($pdo, $bookingId) || (int) $bookingId < 1) {
    return;
}
$bookingAiDesign = null;
try {
    $statement = $pdo->prepare(
        "SELECT d.id, d.image_path, d.design_number, r.nail_shape,
                r.nail_length, r.colors, r.design_style, r.occasion,
                r.add_ons, r.instructions, u.fullname AS customer_name
         FROM bookings b
         INNER JOIN ai_generated_designs d ON d.id=b.selected_ai_design_id
         INNER JOIN ai_design_requests r ON r.id=d.request_id
         INNER JOIN users u ON u.id=b.customer_id
         WHERE b.id=? LIMIT 1"
    );
    $statement->execute([(int) $bookingId]);
    $bookingAiDesign = $statement->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $exception) {
    $bookingAiDesign = null;
}
?>
<?php if ($bookingAiDesign): ?>
    <section class="booking-ai-card">
        <div class="booking-ai-image">
            <a href="<?= htmlspecialchars($bookingAiDesign['image_path']) ?>" target="_blank">
                <img src="<?= htmlspecialchars($bookingAiDesign['image_path']) ?>"
                     alt="Customer selected AI nail design">
            </a>
        </div>
        <div class="booking-ai-details">
            <p class="booking-ai-kicker">Customer design reference</p>
            <h2><?= htmlspecialchars($bookingAiDesign['design_style']) ?></h2>
            <dl>
                <div><dt>Customer</dt><dd><?= htmlspecialchars($bookingAiDesign['customer_name']) ?></dd></div>
                <div><dt>Shape</dt><dd><?= htmlspecialchars($bookingAiDesign['nail_shape']) ?></dd></div>
                <div><dt>Length</dt><dd><?= htmlspecialchars($bookingAiDesign['nail_length']) ?></dd></div>
                <div><dt>Color</dt><dd><?= htmlspecialchars($bookingAiDesign['colors']) ?></dd></div>
                <div><dt>Occasion</dt><dd><?= htmlspecialchars($bookingAiDesign['occasion'] ?: 'Not specified') ?></dd></div>
            </dl>
            <?php if ($bookingAiDesign['instructions']): ?>
                <div class="booking-ai-notes">
                    <strong>Customer instructions</strong>
                    <p><?= nl2br(htmlspecialchars($bookingAiDesign['instructions'])) ?></p>
                </div>
            <?php endif; ?>
            <a class="btn btn-gold" href="<?= htmlspecialchars($bookingAiDesign['image_path']) ?>"
               target="_blank">View full image</a>
        </div>
    </section>
<?php else: ?>
    <section class="booking-ai-empty">
        <h2>No AI design attached</h2>
        <p>This customer did not attach an AI Nail Studio reference to the appointment.</p>
    </section>
<?php endif; ?>

