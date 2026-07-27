<?php
declare(strict_types=1);

require_once __DIR__ . '/booking-management-helper.php';

function renderCustomerBookingTimeline(
    PDO $pdo,
    array $booking
): void {
    $current = (string) ($booking['status'] ?? 'pending');
    $history = bookingManagementHistory($pdo, (int) ($booking['id'] ?? 0));
    $historyDates = [];
    foreach ($history as $event) {
        $historyDates[(string) $event['to_status']] = (string) $event['created_at'];
    }

    if (in_array($current, ['cancelled', 'no_show'], true)) {
        $steps = [
            ['pending', 'Received'],
            [$current, bookingManagementStatusLabel($current)],
        ];
    } else {
        $steps = [
            ['pending', 'Received'],
            ['confirmed', 'Confirmed'],
            ['in_progress', 'In Service'],
            ['completed', 'Completed'],
        ];
    }

    $order = array_column($steps, 0);
    $currentIndex = array_search($current, $order, true);
    if ($currentIndex === false) {
        $currentIndex = 0;
    }
    ?>
    <section class="customer-ui-panel customer-span-12 customer-booking-timeline-panel">
        <div class="customer-panel-title">
            <div>
                <p class="customer-kicker">Live progress</p>
                <h2>Booking timeline</h2>
                <p>The salon updates this timeline as your appointment progresses.</p>
            </div>
        </div>
        <ol class="customer-booking-timeline">
            <?php foreach ($steps as $index => [$status, $label]): ?>
                <?php
                $class = $index < $currentIndex
                    ? 'done'
                    : ($index === $currentIndex ? 'current' : '');
                $date = $historyDates[$status] ?? null;
                if ($status === 'pending' && !$date) {
                    $date = $booking['created_at'] ?? null;
                }
                ?>
                <li class="<?= $class ?>">
                    <i><?= $index < $currentIndex ? '✓' : $index + 1 ?></i>
                    <strong><?= htmlspecialchars($label) ?></strong>
                    <small>
                        <?= $date
                            ? date('M j · g:i A', strtotime($date))
                            : ($index > $currentIndex ? 'Waiting' : 'Current') ?>
                    </small>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>
    <?php
}
