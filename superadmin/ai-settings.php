<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
require_once '../includes/ai-service.php';
require_once '../includes/audit-helper.php';

$pageTitle = 'AI Settings';
$message = '';
$error = '';

if (empty($_SESSION['ai_settings_csrf'])) {
    $_SESSION['ai_settings_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals(
        (string) $_SESSION['ai_settings_csrf'],
        (string) ($_POST['csrf_token'] ?? '')
    )) {
        $error = 'Your session token expired. Refresh the page and try again.';
    } else {
        $url = trim((string) ($_POST['ai_service_url'] ?? ''));
        $limit = filter_var(
            $_POST['daily_customer_limit'] ?? null,
            FILTER_VALIDATE_INT
        );
        $timeout = filter_var(
            $_POST['request_timeout_seconds'] ?? null,
            FILTER_VALIDATE_INT
        );

        try {
            $url = aiLocalServiceUrl($url);

            if ($limit === false || $limit < 1 || $limit > 100) {
                throw new InvalidArgumentException(
                    'Daily limit must be between 1 and 100.'
                );
            }

            if ($timeout === false || $timeout < 30 || $timeout > 300) {
                throw new InvalidArgumentException(
                    'Request timeout must be between 30 and 300 seconds.'
                );
            }

            $values = [
                'ai_enabled' => isset($_POST['ai_enabled']) ? '1' : '0',
                'nail_generator_enabled' =>
                    isset($_POST['nail_generator_enabled']) ? '1' : '0',
                'lip_preview_enabled' =>
                    isset($_POST['lip_preview_enabled']) ? '1' : '0',
                'eyebrow_preview_enabled' =>
                    isset($_POST['eyebrow_preview_enabled']) ? '1' : '0',
                'chatbot_enabled' =>
                    isset($_POST['chatbot_enabled']) ? '1' : '0',
                'receipt_verification_enabled' =>
                    isset($_POST['receipt_verification_enabled']) ? '1' : '0',
                'style_recommendation_enabled' =>
                    isset($_POST['style_recommendation_enabled']) ? '1' : '0',
                'ai_service_url' => $url,
                'daily_customer_limit' => (string) $limit,
                'images_per_request' => '3',
                'request_timeout_seconds' => (string) $timeout,
            ];

            foreach ($values as $key => $value) {
                saveAiSetting(
                    $pdo,
                    $key,
                    $value,
                    (int) $_SESSION['user_id']
                );
            }

            writeAuditLog(
                $pdo,
                'Updated AI settings',
                'AI Settings',
                'AI availability and Python service connection were updated.'
            );

            $message = 'AI settings saved successfully.';
        } catch (Throwable $exception) {
            $error = $exception->getMessage();
        }
    }
}

$settings = aiSettings($pdo);
$connection = [
    'success' => false,
    'provider' => 'Unavailable',
    'model' => 'Unavailable',
    'configured' => false,
    'message' => 'Python AI service is offline.',
];

try {
    $connection = callAiService(
        $settings['ai_service_url'],
        '/api/health',
        null,
        5
    );
} catch (Throwable $exception) {
    $connection['message'] = $exception->getMessage();
}

$modules = [
    [
        'nail_generator_enabled',
        'AI Nail Art Generator',
        'Generate three nail-art concepts from customer preferences.',
        'Ready',
    ],
    [
        'lip_preview_enabled',
        'AI Lip Preview',
        'Provide color and finish previews from an uploaded photo.',
        'Ready',
    ],
    [
        'eyebrow_preview_enabled',
        'AI Eyebrow Preview',
        'Preview eyebrow shape, thickness, and color.',
        'Ready',
    ],
    [
        'chatbot_enabled',
        'FAQ Chatbot',
        'Answer common questions about services, pricing, and bookings.',
        'Foundation only',
    ],
    [
        'receipt_verification_enabled',
        'Receipt Verification',
        'Prepare uploaded payment receipts for automated verification.',
        'Foundation only',
    ],
    [
        'style_recommendation_enabled',
        'Style Recommendation',
        'Recommend styles using customer preferences and service history.',
        'Foundation only',
    ],
];

include '../includes/superadmin-header.php';
?>
<link rel="stylesheet" href="../assets/css/superadmin-ai-settings.css">

<div class="dashboard">
    <?php include '../includes/superadmin-sidebar.php'; ?>

    <main class="content">
        <div class="page-header">
            <div>
                <h1 class="gold-title">AI Settings</h1>
                <p>Control AI modules, customer limits, and the local Python service.</p>
            </div>

            <span
                id="aiServiceBadge"
                class="ai-service-badge <?= !empty($connection['success'])
                    ? 'is-online'
                    : 'is-offline' ?>"
            >
                <span class="ai-service-dot" aria-hidden="true"></span>
                <span id="aiServiceBadgeText">
                    <?= !empty($connection['success'])
                        ? 'Python service online'
                        : 'Python service offline' ?>
                </span>
            </span>
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

        <section class="card settings-card ai-settings-page">
            <form method="POST">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(
                        (string) $_SESSION['ai_settings_csrf'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >

                <div class="settings-section">
                    <div class="settings-section-heading">
                        <div>
                            <h3>AI System</h3>
                            <p>
                                Use the master switch to make all AI features
                                available or unavailable throughout SmartBooking.
                            </p>
                        </div>
                    </div>

                    <div class="toggle-grid ai-master-grid">
                        <label class="toggle-card ai-toggle-card">
                            <input
                                type="checkbox"
                                name="ai_enabled"
                                <?= $settings['ai_enabled'] === '1'
                                    ? 'checked'
                                    : '' ?>
                            >
                            <span>
                                <strong>Enable SmartBooking AI</strong>
                                <small>
                                    Allow enabled AI modules to appear in the
                                    customer and management interfaces.
                                </small>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="settings-section">
                    <div class="settings-section-heading">
                        <div>
                            <h3>AI Modules</h3>
                            <p>
                                Choose which virtual previews and intelligent
                                tools are available to users.
                            </p>
                        </div>
                    </div>

                    <div class="toggle-grid">
                        <?php foreach ($modules as [$key, $label, $copy, $status]): ?>
                            <label class="toggle-card ai-toggle-card">
                                <input
                                    type="checkbox"
                                    name="<?= htmlspecialchars(
                                        $key,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    <?= $settings[$key] === '1'
                                        ? 'checked'
                                        : '' ?>
                                >
                                <span>
                                    <span class="ai-module-title">
                                        <strong><?= htmlspecialchars(
                                            $label,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?></strong>
                                        <em class="<?= $status === 'Ready'
                                            ? 'is-ready'
                                            : 'is-foundation' ?>">
                                            <?= htmlspecialchars(
                                                $status,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </em>
                                    </span>
                                    <small><?= htmlspecialchars(
                                        $copy,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></small>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="settings-section">
                    <div class="settings-section-heading ai-connection-heading">
                        <div>
                            <h3>Python AI Service</h3>
                            <p>
                                Live connection information from the configured
                                local Python service.
                            </p>
                        </div>

                        <button
                            id="refreshAiStatus"
                            class="btn btn-light"
                            type="button"
                        >
                            Refresh Status
                        </button>
                    </div>

                    <div
                        id="aiHealthSummary"
                        class="ai-health-summary"
                        data-health-url="api/ai-health.php"
                    >
                        <div class="ai-health-item">
                            <small>Status</small>
                            <strong id="aiHealthStatus">
                                <?= !empty($connection['success'])
                                    ? 'Healthy'
                                    : 'Offline' ?>
                            </strong>
                        </div>
                        <div class="ai-health-item">
                            <small>Provider</small>
                            <strong id="aiHealthProvider">
                                <?= htmlspecialchars(
                                    (string) ($connection['provider']
                                        ?? 'Unavailable'),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </strong>
                        </div>
                        <div class="ai-health-item">
                            <small>Model</small>
                            <strong id="aiHealthModel">
                                <?= htmlspecialchars(
                                    (string) ($connection['model']
                                        ?? 'Unavailable'),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </strong>
                        </div>
                        <div class="ai-health-item">
                            <small>Configured</small>
                            <strong id="aiHealthConfigured">
                                <?= !empty($connection['configured'])
                                    ? 'Yes'
                                    : 'No' ?>
                            </strong>
                        </div>
                    </div>

                    <p
                        id="aiHealthMessage"
                        class="ai-health-message"
                        <?= !empty($connection['success']) ? 'hidden' : '' ?>
                    >
                        <?= htmlspecialchars(
                            (string) ($connection['message']
                                ?? 'Start the Python AI service.'),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>
                </div>

                <div class="settings-section">
                    <div class="settings-section-heading">
                        <div>
                            <h3>Service Configuration</h3>
                            <p>
                                Provider credentials remain securely stored in
                                <code>python_ai/.env</code>.
                            </p>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group form-span-2">
                            <label for="ai_service_url">Python Service URL</label>
                            <input
                                id="ai_service_url"
                                name="ai_service_url"
                                type="url"
                                required
                                value="<?= htmlspecialchars(
                                    $settings['ai_service_url'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >
                            <small class="field-note">
                                Local development accepts localhost or
                                127.0.0.1 only.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="daily_customer_limit">
                                Daily Generations Per Customer
                            </label>
                            <input
                                id="daily_customer_limit"
                                name="daily_customer_limit"
                                type="number"
                                min="1"
                                max="100"
                                value="<?= (int) $settings[
                                    'daily_customer_limit'
                                ] ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="request_timeout_seconds">
                                Request Timeout (Seconds)
                            </label>
                            <input
                                id="request_timeout_seconds"
                                name="request_timeout_seconds"
                                type="number"
                                min="30"
                                max="300"
                                value="<?= (int) $settings[
                                    'request_timeout_seconds'
                                ] ?>"
                            >
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-gold" type="submit">
                        Save AI Settings
                    </button>
                </div>
            </form>
        </section>

        <script defer src="../assets/js/superadmin-ai-settings.js"></script>
        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>
