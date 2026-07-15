<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
require_once '../includes/audit-helper.php';

$pageTitle = 'System Settings';
$message = '';
$error = '';

$defaults = [
    'salon_name' => 'Nails & Body Worx',
    'contact_number' => '',
    'email' => '',
    'address' => '',
    'facebook_url' => '',
    'opening_time' => '09:00',
    'closing_time' => '18:00',
    'downpayment_percentage' => '50',
    'grace_period_minutes' => '15',
    'default_language' => 'English',
    'timezone' => 'Asia/Manila',
];

$statement = $pdo->query("SELECT * FROM system_settings WHERE id = 1 LIMIT 1");
$settings = $statement->fetch() ?: $defaults;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $salonName = trim($_POST['salon_name'] ?? '');
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $facebookUrl = trim($_POST['facebook_url'] ?? '');
    $openingTime = $_POST['opening_time'] ?? '';
    $closingTime = $_POST['closing_time'] ?? '';
    $downpaymentPercentage = filter_input(INPUT_POST, 'downpayment_percentage', FILTER_VALIDATE_INT);
    $gracePeriodMinutes = filter_input(INPUT_POST, 'grace_period_minutes', FILTER_VALIDATE_INT);
    $defaultLanguage = $_POST['default_language'] ?? 'English';
    $timezone = $_POST['timezone'] ?? 'Asia/Manila';

    if ($salonName === '') {
        $error = 'Salon name is required.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($downpaymentPercentage === false || $downpaymentPercentage < 0 || $downpaymentPercentage > 100) {
        $error = 'Down payment percentage must be between 0 and 100.';
    } elseif ($gracePeriodMinutes === false || $gracePeriodMinutes < 0 || $gracePeriodMinutes > 120) {
        $error = 'Grace period must be between 0 and 120 minutes.';
    } else {
        $statement = $pdo->prepare(
            "INSERT INTO system_settings (
                id,
                salon_name,
                contact_number,
                email,
                address,
                facebook_url,
                opening_time,
                closing_time,
                downpayment_percentage,
                grace_period_minutes,
                default_language,
                timezone
            ) VALUES (
                1,
                :salon_name,
                :contact_number,
                :email,
                :address,
                :facebook_url,
                :opening_time,
                :closing_time,
                :downpayment_percentage,
                :grace_period_minutes,
                :default_language,
                :timezone
            )
            ON DUPLICATE KEY UPDATE
                salon_name = VALUES(salon_name),
                contact_number = VALUES(contact_number),
                email = VALUES(email),
                address = VALUES(address),
                facebook_url = VALUES(facebook_url),
                opening_time = VALUES(opening_time),
                closing_time = VALUES(closing_time),
                downpayment_percentage = VALUES(downpayment_percentage),
                grace_period_minutes = VALUES(grace_period_minutes),
                default_language = VALUES(default_language),
                timezone = VALUES(timezone)"
        );

        $statement->execute([
            'salon_name' => $salonName,
            'contact_number' => $contactNumber,
            'email' => $email,
            'address' => $address,
            'facebook_url' => $facebookUrl,
            'opening_time' => $openingTime,
            'closing_time' => $closingTime,
            'downpayment_percentage' => $downpaymentPercentage,
            'grace_period_minutes' => $gracePeriodMinutes,
            'default_language' => $defaultLanguage,
            'timezone' => $timezone,
        ]);

        writeAuditLog(
            $pdo,
            'Updated system settings',
            'System Settings',
            'Salon information and booking rules were updated.'
        );

        $message = 'System settings saved successfully.';

        $statement = $pdo->query("SELECT * FROM system_settings WHERE id = 1 LIMIT 1");
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
                <h1 class="gold-title">System Settings</h1>
                <p>Configure salon information, operating hours, and booking rules.</p>
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
                <div class="settings-section">
                    <h3>Business Information</h3>

                    <div class="form-grid">
                        <div class="form-group form-span-2">
                            <label for="salon_name">Salon Name</label>
                            <input id="salon_name" name="salon_name" type="text"
                                   value="<?= htmlspecialchars($settings['salon_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <input id="contact_number" name="contact_number" type="text"
                                   value="<?= htmlspecialchars($settings['contact_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="form-group">
                            <label for="email">Business Email</label>
                            <input id="email" name="email" type="email"
                                   value="<?= htmlspecialchars($settings['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="form-group form-span-2">
                            <label for="address">Business Address</label>
                            <textarea id="address" name="address" rows="3"><?= htmlspecialchars($settings['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>

                        <div class="form-group form-span-2">
                            <label for="facebook_url">Facebook Page URL</label>
                            <input id="facebook_url" name="facebook_url" type="url"
                                   value="<?= htmlspecialchars($settings['facebook_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>Operating Hours</h3>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="opening_time">Opening Time</label>
                            <input id="opening_time" name="opening_time" type="time"
                                   value="<?= htmlspecialchars($settings['opening_time'] ?? '09:00', ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="form-group">
                            <label for="closing_time">Closing Time</label>
                            <input id="closing_time" name="closing_time" type="time"
                                   value="<?= htmlspecialchars($settings['closing_time'] ?? '18:00', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>Booking Rules</h3>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="downpayment_percentage">Down Payment Percentage</label>
                            <input id="downpayment_percentage" name="downpayment_percentage"
                                   type="number" min="0" max="100"
                                   value="<?= (int) ($settings['downpayment_percentage'] ?? 50) ?>">
                        </div>

                        <div class="form-group">
                            <label for="grace_period_minutes">Grace Period (Minutes)</label>
                            <input id="grace_period_minutes" name="grace_period_minutes"
                                   type="number" min="0" max="120"
                                   value="<?= (int) ($settings['grace_period_minutes'] ?? 15) ?>">
                        </div>

                        <div class="form-group">
                            <label for="default_language">Default Language</label>
                            <select id="default_language" name="default_language">
                                <?php foreach (['English', 'Filipino'] as $language): ?>
                                    <option value="<?= $language ?>"
                                        <?= ($settings['default_language'] ?? 'English') === $language ? 'selected' : '' ?>>
                                        <?= $language ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="timezone">Timezone</label>
                            <select id="timezone" name="timezone">
                                <option value="Asia/Manila"
                                    <?= ($settings['timezone'] ?? 'Asia/Manila') === 'Asia/Manila' ? 'selected' : '' ?>>
                                    Asia/Manila
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-gold" type="submit">Save System Settings</button>
                </div>
            </form>
        </section>

        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>
