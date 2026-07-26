<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
require_once '../includes/audit-helper.php';

date_default_timezone_set('Asia/Manila');

$pageTitle = 'System Settings';
$message = '';
$error = '';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['system_settings_csrf'])) {
    $_SESSION['system_settings_csrf'] = bin2hex(random_bytes(32));
}

function settingsEscape(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function settingsColumns(PDO $pdo): array
{
    $columns = [];
    foreach ($pdo->query('SHOW COLUMNS FROM system_settings')->fetchAll() as $column) {
        $columns[] = (string) $column['Field'];
    }
    return $columns;
}

function loadSystemSettings(PDO $pdo, array $defaults, bool $keyValueSchema): array
{
    if ($keyValueSchema) {
        $rows = $pdo->query(
            'SELECT setting_key, setting_value FROM system_settings'
        )->fetchAll();

        $stored = [];
        foreach ($rows as $row) {
            $stored[(string) $row['setting_key']] =
                $row['setting_value'] ?? '';
        }

        return array_merge($defaults, $stored);
    }

    $row = $pdo->query(
        'SELECT * FROM system_settings WHERE id = 1 LIMIT 1'
    )->fetch() ?: [];

    return array_merge($defaults, $row);
}

function saveKeyValueSetting(
    PDO $pdo,
    string $key,
    mixed $value,
    string $type,
    string $group,
    bool $public,
    ?int $userId
): void {
    $statement = $pdo->prepare(
        'INSERT INTO system_settings (
            setting_key,
            setting_value,
            value_type,
            setting_group,
            is_public,
            updated_by
        ) VALUES (
            :setting_key,
            :setting_value,
            :value_type,
            :setting_group,
            :is_public,
            :updated_by
        )
        ON DUPLICATE KEY UPDATE
            setting_value = VALUES(setting_value),
            value_type = VALUES(value_type),
            setting_group = VALUES(setting_group),
            is_public = VALUES(is_public),
            updated_by = VALUES(updated_by)'
    );

    $statement->execute([
        'setting_key' => $key,
        'setting_value' => (string) $value,
        'value_type' => $type,
        'setting_group' => $group,
        'is_public' => $public ? 1 : 0,
        'updated_by' => $userId,
    ]);
}

$defaults = [
    'salon_name' => 'Nails & Body Worx',
    'business_phone' => '',
    'business_email' => '',
    'business_address' => '',
    'facebook_url' => '',
    'opening_time' => '09:00',
    'closing_time' => '18:00',
    'downpayment_percentage' => '50',
    'grace_period_minutes' => '15',
    'reminder_minutes_before' => '30',
    'booking_cancellation_hours' => '24',
    'default_language' => 'english',
    'default_theme' => 'default',
    'timezone' => 'Asia/Manila',
    'currency' => 'PHP',
];

$columns = settingsColumns($pdo);
$keyValueSchema = in_array('setting_key', $columns, true)
    && in_array('setting_value', $columns, true);

/*
 * Map old field names to the master database keys so this page remains
 * compatible with projects that previously used contact_number/email/address.
 */
$settings = loadSystemSettings($pdo, $defaults, $keyValueSchema);
$settings['business_phone'] = $settings['business_phone']
    ?? $settings['contact_number']
    ?? '';
$settings['business_email'] = $settings['business_email']
    ?? $settings['email']
    ?? '';
$settings['business_address'] = $settings['business_address']
    ?? $settings['address']
    ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = (string) ($_POST['csrf_token'] ?? '');

    $values = [
        'salon_name' => trim((string) ($_POST['salon_name'] ?? '')),
        'business_phone' => trim((string) ($_POST['business_phone'] ?? '')),
        'business_email' => trim((string) ($_POST['business_email'] ?? '')),
        'business_address' => trim((string) ($_POST['business_address'] ?? '')),
        'facebook_url' => trim((string) ($_POST['facebook_url'] ?? '')),
        'opening_time' => trim((string) ($_POST['opening_time'] ?? '09:00')),
        'closing_time' => trim((string) ($_POST['closing_time'] ?? '18:00')),
        'downpayment_percentage' =>
            filter_input(INPUT_POST, 'downpayment_percentage', FILTER_VALIDATE_INT),
        'grace_period_minutes' =>
            filter_input(INPUT_POST, 'grace_period_minutes', FILTER_VALIDATE_INT),
        'reminder_minutes_before' =>
            filter_input(INPUT_POST, 'reminder_minutes_before', FILTER_VALIDATE_INT),
        'booking_cancellation_hours' =>
            filter_input(INPUT_POST, 'booking_cancellation_hours', FILTER_VALIDATE_INT),
        'default_language' =>
            strtolower(trim((string) ($_POST['default_language'] ?? 'english'))),
        'default_theme' =>
            strtolower(trim((string) ($_POST['default_theme'] ?? 'default'))),
        /*
         * SmartBooking V2 is configured exclusively for Philippine operations.
         * These values remain available to reports, bookings, and receipts, but
         * are intentionally not editable through the interface.
         */
        'timezone' => 'Asia/Manila',
        'currency' => 'PHP',
    ];

    if (!hash_equals($_SESSION['system_settings_csrf'], $csrf)) {
        $error = 'Your session token expired. Refresh the page and try again.';
    } elseif ($values['salon_name'] === '') {
        $error = 'Salon name is required.';
    } elseif (
        $values['business_email'] !== ''
        && !filter_var($values['business_email'], FILTER_VALIDATE_EMAIL)
    ) {
        $error = 'Please enter a valid business email address.';
    } elseif (
        $values['facebook_url'] !== ''
        && !filter_var($values['facebook_url'], FILTER_VALIDATE_URL)
    ) {
        $error = 'Please enter a valid Facebook page URL.';
    } elseif (
        $values['downpayment_percentage'] === false
        || $values['downpayment_percentage'] < 0
        || $values['downpayment_percentage'] > 100
    ) {
        $error = 'Down payment percentage must be between 0 and 100.';
    } elseif (
        $values['grace_period_minutes'] === false
        || $values['grace_period_minutes'] < 0
        || $values['grace_period_minutes'] > 120
    ) {
        $error = 'Grace period must be between 0 and 120 minutes.';
    } elseif (
        $values['reminder_minutes_before'] === false
        || $values['reminder_minutes_before'] < 0
        || $values['reminder_minutes_before'] > 10080
    ) {
        $error = 'Reminder time must be between 0 and 10,080 minutes.';
    } elseif (
        $values['booking_cancellation_hours'] === false
        || $values['booking_cancellation_hours'] < 0
        || $values['booking_cancellation_hours'] > 720
    ) {
        $error = 'Cancellation limit must be between 0 and 720 hours.';
    } elseif (
        !in_array(
            $values['default_language'],
            ['english', 'filipino', 'taglish'],
            true
        )
    ) {
        $error = 'Please select a valid default language.';
    } elseif (
        !in_array($values['default_theme'], ['default', 'light', 'dark'], true)
    ) {
        $error = 'Please select a valid default appearance.';
    } elseif ($values['opening_time'] >= $values['closing_time']) {
        $error = 'Closing time must be later than opening time.';
    } else {
        try {
            $pdo->beginTransaction();
            $userId = isset($_SESSION['user_id'])
                ? (int) $_SESSION['user_id']
                : null;

            if ($keyValueSchema) {
                $metadata = [
                    'salon_name' => ['string', 'business', true],
                    'business_phone' => ['string', 'business', true],
                    'business_email' => ['string', 'business', true],
                    'business_address' => ['string', 'business', true],
                    'facebook_url' => ['string', 'business', true],
                    'opening_time' => ['string', 'operating_hours', true],
                    'closing_time' => ['string', 'operating_hours', true],
                    'downpayment_percentage' => ['decimal', 'booking', true],
                    'grace_period_minutes' => ['integer', 'booking', true],
                    'reminder_minutes_before' => ['integer', 'booking', true],
                    'booking_cancellation_hours' => ['integer', 'booking', true],
                    'default_language' => ['string', 'localization', true],
                    'default_theme' => ['string', 'appearance', true],
                    'timezone' => ['string', 'localization', true],
                    'currency' => ['string', 'localization', true],
                ];

                foreach ($values as $key => $value) {
                    [$type, $group, $public] = $metadata[$key];
                    saveKeyValueSetting(
                        $pdo,
                        $key,
                        $value,
                        $type,
                        $group,
                        $public,
                        $userId
                    );
                }
            } else {
                $statement = $pdo->prepare(
                    'UPDATE system_settings SET
                        salon_name = :salon_name,
                        contact_number = :business_phone,
                        email = :business_email,
                        address = :business_address,
                        facebook_url = :facebook_url,
                        opening_time = :opening_time,
                        closing_time = :closing_time,
                        downpayment_percentage = :downpayment_percentage,
                        grace_period_minutes = :grace_period_minutes,
                        default_language = :default_language,
                        default_theme = :default_theme,
                        timezone = :timezone
                     WHERE id = 1'
                );
                $statement->execute([
                    'salon_name' => $values['salon_name'],
                    'business_phone' => $values['business_phone'],
                    'business_email' => $values['business_email'],
                    'business_address' => $values['business_address'],
                    'facebook_url' => $values['facebook_url'],
                    'opening_time' => $values['opening_time'],
                    'closing_time' => $values['closing_time'],
                    'downpayment_percentage' =>
                        $values['downpayment_percentage'],
                    'grace_period_minutes' =>
                        $values['grace_period_minutes'],
                    'default_language' => $values['default_language'],
                    'default_theme' => $values['default_theme'],
                    'timezone' => $values['timezone'],
                ]);
            }

            $pdo->commit();

            if (function_exists('writeAuditLog')) {
                writeAuditLog(
                    $pdo,
                    'Updated system settings',
                    'System Settings',
                    'Business information, operating hours, booking rules, and visitor defaults were updated.'
                );
            }

            $message = 'System settings saved successfully.';
            $settings = loadSystemSettings($pdo, $defaults, $keyValueSchema);
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'The settings could not be saved. Please try again.';
            error_log('System settings save failed: ' . $exception->getMessage());
        }
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
                <p>Configure salon information, operating hours, booking rules, and visitor defaults.</p>
            </div>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert alert-success"><?= settingsEscape($message) ?></div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?= settingsEscape($error) ?></div>
        <?php endif; ?>

        <section class="card settings-card">
            <form method="POST">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= settingsEscape($_SESSION['system_settings_csrf']) ?>"
                >

                <div class="settings-section">
                    <h3>Business Information</h3>
                    <div class="form-grid">
                        <div class="form-group form-span-2">
                            <label for="salon_name">Salon Name</label>
                            <input id="salon_name" name="salon_name" type="text"
                                value="<?= settingsEscape($settings['salon_name'] ?? '') ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="business_phone">Contact Number</label>
                            <input id="business_phone" name="business_phone" type="tel"
                                value="<?= settingsEscape($settings['business_phone'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="business_email">Business Email</label>
                            <input id="business_email" name="business_email" type="email"
                                value="<?= settingsEscape($settings['business_email'] ?? '') ?>">
                        </div>

                        <div class="form-group form-span-2">
                            <label for="business_address">Business Address</label>
                            <textarea id="business_address" name="business_address"
                                rows="3"><?= settingsEscape($settings['business_address'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group form-span-2">
                            <label for="facebook_url">Facebook Page URL</label>
                            <input id="facebook_url" name="facebook_url" type="url"
                                value="<?= settingsEscape($settings['facebook_url'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>Operating Hours</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="opening_time">Opening Time</label>
                            <input id="opening_time" name="opening_time" type="time"
                                value="<?= settingsEscape(substr((string) ($settings['opening_time'] ?? '09:00'), 0, 5)) ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="closing_time">Closing Time</label>
                            <input id="closing_time" name="closing_time" type="time"
                                value="<?= settingsEscape(substr((string) ($settings['closing_time'] ?? '18:00'), 0, 5)) ?>"
                                required>
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>Booking Rules</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="downpayment_percentage">Down Payment Percentage</label>
                            <input id="downpayment_percentage"
                                name="downpayment_percentage" type="number"
                                min="0" max="100"
                                value="<?= (int) ($settings['downpayment_percentage'] ?? 50) ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="grace_period_minutes">Grace Period (Minutes)</label>
                            <input id="grace_period_minutes"
                                name="grace_period_minutes" type="number"
                                min="0" max="120"
                                value="<?= (int) ($settings['grace_period_minutes'] ?? 15) ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="reminder_minutes_before">Reminder Before Appointment</label>
                            <input id="reminder_minutes_before"
                                name="reminder_minutes_before" type="number"
                                min="0" max="10080"
                                value="<?= (int) ($settings['reminder_minutes_before'] ?? 30) ?>"
                                required>
                            <small class="field-note">Number of minutes before the appointment.</small>
                        </div>

                        <div class="form-group">
                            <label for="booking_cancellation_hours">Cancellation Limit</label>
                            <input id="booking_cancellation_hours"
                                name="booking_cancellation_hours" type="number"
                                min="0" max="720"
                                value="<?= (int) ($settings['booking_cancellation_hours'] ?? 24) ?>"
                                required>
                            <small class="field-note">Minimum hours before an appointment can be cancelled.</small>
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <div class="settings-section-heading">
                        <div>
                            <h3>Public Website Defaults</h3>
                            <p>
                                These are fallback values for visitors who have not
                                chosen personal language or appearance preferences.
                            </p>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="default_language">Default Website Language</label>
                            <select id="default_language" name="default_language">
                                <?php foreach ([
                                    'english' => 'English',
                                    'filipino' => 'Filipino',
                                    'taglish' => 'Taglish',
                                ] as $value => $label): ?>
                                    <option value="<?= settingsEscape($value) ?>"
                                        <?= strtolower((string) ($settings['default_language'] ?? 'english')) === $value ? 'selected' : '' ?>>
                                        <?= settingsEscape($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="default_theme">Default Website Appearance</label>
                            <select id="default_theme" name="default_theme">
                                <?php foreach ([
                                    'default' => 'Default — Brown, Gold & Cream',
                                    'light' => 'Light — Bright & High Contrast',
                                    'dark' => 'Dark — Reduced Brightness',
                                ] as $value => $label): ?>
                                    <option value="<?= settingsEscape($value) ?>"
                                        <?= strtolower((string) ($settings['default_theme'] ?? 'default')) === $value ? 'selected' : '' ?>>
                                        <?= settingsEscape($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-gold" type="submit">
                        Save System Settings
                    </button>
                </div>
            </form>
        </section>

        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>
