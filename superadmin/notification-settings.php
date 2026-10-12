<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
require_once '../includes/audit-helper.php';

$pageTitle = 'Notification Settings';
$message = '';
$error = '';
$_SESSION['notification_settings_csrf'] ??= bin2hex(random_bytes(32));
$deliverySummary = [];
$deliveryProblems = [];
try {
 $deliverySummary = $pdo->query('SELECT state,COUNT(*) AS total FROM notification_deliveries GROUP BY state')->fetchAll();
 $deliveryProblems = $pdo->query("SELECT id,channel,state,last_error FROM notification_deliveries WHERE state IN ('failed','uncertain','retry') ORDER BY id DESC LIMIT 10")->fetchAll();
} catch(Throwable $e) {}

$defaults = [
    'sms_enabled' => 1,
    'email_enabled' => 1,
    'push_enabled' => 1,
    'booking_confirmation_enabled' => 1,
    'payment_confirmation_enabled' => 1,
    'promotion_enabled' => 1,
    'reminder_minutes' => 30,
    'sms_sender_name' => 'NBW',
    'reply_to_email' => '',
];

$statement = $pdo->query("SELECT * FROM notification_settings WHERE id = 1 LIMIT 1");
$settings = $statement->fetch() ?: $defaults;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['notification_settings_csrf'], (string)($_POST['csrf_token'] ?? ''))) {
    $error = 'Your session expired. Refresh and try again.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $smsEnabled = isset($_POST['sms_enabled']) ? 1 : 0;
    $emailEnabled = isset($_POST['email_enabled']) ? 1 : 0;
    $pushEnabled = isset($_POST['push_enabled']) ? 1 : 0;
    $bookingConfirmationEnabled = isset($_POST['booking_confirmation_enabled']) ? 1 : 0;
    $paymentConfirmationEnabled = isset($_POST['payment_confirmation_enabled']) ? 1 : 0;
    $promotionEnabled = isset($_POST['promotion_enabled']) ? 1 : 0;
    $reminderMinutes = filter_input(INPUT_POST, 'reminder_minutes', FILTER_VALIDATE_INT);
    $smsSenderName = trim($_POST['sms_sender_name'] ?? '');
    $replyToEmail = trim($_POST['reply_to_email'] ?? '');

    if ($reminderMinutes === false || $reminderMinutes < 5 || $reminderMinutes > 10080) {
        $error = 'Reminder time must be between 5 minutes and 7 days.';
    } elseif ($replyToEmail !== '' && !filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid reply-to email address.';
    } else {
        $statement = $pdo->prepare(
            "INSERT INTO notification_settings (
                id,
                sms_enabled,
                email_enabled,
                push_enabled,
                booking_confirmation_enabled,
                payment_confirmation_enabled,
                promotion_enabled,
                reminder_minutes,
                sms_sender_name,
                reply_to_email
            ) VALUES (
                1,
                :sms_enabled,
                :email_enabled,
                :push_enabled,
                :booking_confirmation_enabled,
                :payment_confirmation_enabled,
                :promotion_enabled,
                :reminder_minutes,
                :sms_sender_name,
                :reply_to_email
            )
            ON DUPLICATE KEY UPDATE
                sms_enabled = VALUES(sms_enabled),
                email_enabled = VALUES(email_enabled),
                push_enabled = VALUES(push_enabled),
                booking_confirmation_enabled = VALUES(booking_confirmation_enabled),
                payment_confirmation_enabled = VALUES(payment_confirmation_enabled),
                promotion_enabled = VALUES(promotion_enabled),
                reminder_minutes = VALUES(reminder_minutes),
                sms_sender_name = VALUES(sms_sender_name),
                reply_to_email = VALUES(reply_to_email)"
        );

        $statement->execute([
            'sms_enabled' => $smsEnabled,
            'email_enabled' => $emailEnabled,
            'push_enabled' => $pushEnabled,
            'booking_confirmation_enabled' => $bookingConfirmationEnabled,
            'payment_confirmation_enabled' => $paymentConfirmationEnabled,
            'promotion_enabled' => $promotionEnabled,
            'reminder_minutes' => $reminderMinutes,
            'sms_sender_name' => $smsSenderName,
            'reply_to_email' => $replyToEmail,
        ]);

        writeAuditLog(
            $pdo,
            'Updated notification settings',
            'Notification Settings',
            'Notification channels and reminder rules were updated.'
        );

        $message = 'Notification settings saved successfully.';

        $statement = $pdo->query("SELECT * FROM notification_settings WHERE id = 1 LIMIT 1");
        $settings = $statement->fetch() ?: $defaults;
    }
}

include '../includes/superadmin-header.php';
?>

<div class="dashboard">
    <?php include '../includes/superadmin-sidebar.php'; ?>

    <main class="content">
        <div class="page-header">
            <div>
                <h1 class="gold-title">Notification Settings</h1>
                <p>Control notification channels, confirmations, reminders, and promotions.</p>
            </div>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <section class="card settings-card">
            <form method="POST">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['notification_settings_csrf'], ENT_QUOTES, 'UTF-8') ?>">
<div class="settings-section"><h2>Appointment message delivery</h2>
<p>Email and SMS are processed by the scheduled notification worker. SMS also needs an enabled provider. Push notifications are not connected.</p>
<p><?php foreach($deliverySummary as $item): ?><?= htmlspecialchars(($item['state']==='sent'?'Accepted by provider':ucfirst($item['state'])), ENT_QUOTES, 'UTF-8') ?>: <?= (int)$item['total'] ?> &nbsp; <?php endforeach; ?></p>
<?php foreach($deliveryProblems as $item): ?><p>#<?= (int)$item['id'] ?> <?= htmlspecialchars($item['channel'].' — '.$item['state'].': '.$item['last_error'], ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?>
</div>
                <div class="settings-section">
                    <h3>Notification Channels</h3>

                    <div class="toggle-grid">
                        <label class="toggle-card">
                            <input type="checkbox" name="sms_enabled"
                                <?= !empty($settings['sms_enabled']) ? 'checked' : '' ?>>
                            <span>
                                <strong>SMS Notifications</strong>
                                <small>Send text messages for confirmations and reminders.</small>
                            </span>
                        </label>

                        <label class="toggle-card">
                            <input type="checkbox" name="email_enabled"
                                <?= !empty($settings['email_enabled']) ? 'checked' : '' ?>>
                            <span>
                                <strong>Email Notifications</strong>
                                <small>Send confirmations, receipts, and updates through email.</small>
                            </span>
                        </label>

                        <label class="toggle-card">
                            <input type="checkbox" name="push_enabled"
                                <?= !empty($settings['push_enabled']) ? 'checked' : '' ?>>
                            <span>
                                <strong>Push Notifications</strong>
                                <small>Prepare notifications for the future mobile application.</small>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>Notification Events</h3>

                    <div class="toggle-grid">
                        <label class="toggle-card">
                            <input type="checkbox" name="booking_confirmation_enabled"
                                <?= !empty($settings['booking_confirmation_enabled']) ? 'checked' : '' ?>>
                            <span>
                                <strong>Booking Confirmation</strong>
                                <small>Notify customers when an appointment is confirmed.</small>
                            </span>
                        </label>

                        <label class="toggle-card">
                            <input type="checkbox" name="payment_confirmation_enabled"
                                <?= !empty($settings['payment_confirmation_enabled']) ? 'checked' : '' ?>>
                            <span>
                                <strong>Payment Confirmation</strong>
                                <small>Notify customers after payment verification.</small>
                            </span>
                        </label>

                        <label class="toggle-card">
                            <input type="checkbox" name="promotion_enabled"
                                <?= !empty($settings['promotion_enabled']) ? 'checked' : '' ?>>
                            <span>
                                <strong>Promotional Notifications</strong>
                                <small>Allow customers to receive offers and seasonal promotions.</small>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>Delivery Configuration</h3>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="reminder_minutes">Appointment Reminder (Minutes Before)</label>
                            <input id="reminder_minutes" name="reminder_minutes"
                                   type="number" min="5" max="10080"
                                   value="<?= (int) ($settings['reminder_minutes'] ?? 30) ?>">
                        </div>

                        <div class="form-group">
                            <label for="sms_sender_name">SMS Sender Name</label>
                            <input id="sms_sender_name" name="sms_sender_name" type="text"
                                   value="<?= htmlspecialchars($settings['sms_sender_name'] ?? 'NBW', ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="form-group form-span-2">
                            <label for="reply_to_email">Reply-To Email</label>
                            <input id="reply_to_email" name="reply_to_email" type="email"
                                   value="<?= htmlspecialchars($settings['reply_to_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-gold" type="submit">Save Notification Settings</button>
                </div>
            </form>
        </section>

        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>
