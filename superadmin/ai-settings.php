<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
require_once '../includes/ai-service.php';
require_once '../includes/audit-helper.php';

$pageTitle = 'AI Settings';
$message = '';
$error = '';
$connection = null;

if (empty($_SESSION['ai_settings_csrf'])) {
    $_SESSION['ai_settings_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals(
        $_SESSION['ai_settings_csrf'],
        (string) ($_POST['csrf_token'] ?? '')
    )) {
        $error = 'Your session token expired. Refresh the page and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? 'save');
        if ($action === 'save') {
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
                aiLocalServiceUrl($url);
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
}

$settings = aiSettings($pdo);

try {
    $connection = callAiService(
        $settings['ai_service_url'],
        '/api/health',
        null,
        5
    );
} catch (Throwable $exception) {
    $connection = [
        'success' => false,
        'message' => $exception->getMessage(),
    ];
}

include '../includes/superadmin-header.php';
?>
<link rel="stylesheet" href="../assets/css/phase2-ai-settings.css">

<div class="dashboard">
    <?php include '../includes/superadmin-sidebar.php'; ?>
    <main class="content">
        <div class="page-header">
            <div>
                <h1 class="gold-title">AI Settings</h1>
                <p>Control SmartBooking AI features and the local Python service.</p>
            </div>
            <span class="ai-status <?= !empty($connection['success']) ? 'online' : 'offline' ?>">
                <?= !empty($connection['success']) ? 'Python service online' : 'Python service offline' ?>
            </span>
        </div>

        <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="post" class="ai-settings-stack">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['ai_settings_csrf']) ?>">
            <input type="hidden" name="action" value="save">

            <section class="card ai-settings-card">
                <div class="ai-section-heading">
                    <div>
                        <h2>AI module availability</h2>
                        <p>Control the available virtual previews and prepared AI modules.</p>
                    </div>
                    <label class="ai-master-toggle">
                        <input type="checkbox" name="ai_enabled" <?= $settings['ai_enabled'] === '1' ? 'checked' : '' ?>>
                        <span>AI system enabled</span>
                    </label>
                </div>

                <div class="ai-module-grid">
                    <?php
                    $modules = [
                        ['nail_generator_enabled', 'AI Nail Art Generator', 'Connected in this Phase 2 package.', true],
                        ['lip_preview_enabled', 'AI Lip Preview', 'Color and finish preview with photo upload.', true],
                        ['eyebrow_preview_enabled', 'AI Eyebrow Preview', 'Shape, thickness, and color preview.', true],
                        ['chatbot_enabled', 'FAQ Chatbot', 'Prepared for a later Phase 2 stage.', false],
                        ['receipt_verification_enabled', 'Receipt Verification', 'Prepared for a later Phase 2 stage.', false],
                        ['style_recommendation_enabled', 'Style Recommendation', 'Prepared for a later Phase 2 stage.', false],
                    ];
                    foreach ($modules as [$key, $label, $copy, $ready]):
                    ?>
                        <label class="ai-module-card">
                            <input type="checkbox" name="<?= $key ?>" <?= $settings[$key] === '1' ? 'checked' : '' ?>>
                            <span>
                                <strong><?= htmlspecialchars($label) ?></strong>
                                <small><?= htmlspecialchars($copy) ?></small>
                                <em class="<?= $ready ? 'ready' : '' ?>"><?= $ready ? 'Ready' : 'Foundation only' ?></em>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="card ai-settings-card">
                <div class="ai-section-heading">
                    <div>
                        <h2>Python AI service</h2>
                        <p>The provider and secret API key stay in <code>python_ai/.env</code>.</p>
                    </div>
                </div>
                <div class="ai-connection-summary">
                    <div><small>Status</small><strong><?= !empty($connection['success']) ? 'Healthy' : 'Offline' ?></strong></div>
                    <div><small>Provider</small><strong><?= htmlspecialchars((string) ($connection['provider'] ?? 'Unavailable')) ?></strong></div>
                    <div><small>Model</small><strong><?= htmlspecialchars((string) ($connection['model'] ?? 'Unavailable')) ?></strong></div>
                    <div><small>Configured</small><strong><?= !empty($connection['configured']) ? 'Yes' : 'No' ?></strong></div>
                </div>

                <div class="ai-form-grid">
                    <label>
                        <span>Python service URL</span>
                        <input type="url" name="ai_service_url" required
                            value="<?= htmlspecialchars($settings['ai_service_url']) ?>">
                    </label>
                    <label>
                        <span>Daily generations per customer</span>
                        <input type="number" name="daily_customer_limit" min="1" max="100"
                            value="<?= (int) $settings['daily_customer_limit'] ?>">
                    </label>
                    <label>
                        <span>Request timeout (seconds)</span>
                        <input type="number" name="request_timeout_seconds" min="30" max="300"
                            value="<?= (int) $settings['request_timeout_seconds'] ?>">
                    </label>
                </div>

                <?php if (empty($connection['success'])): ?>
                    <p class="ai-connection-error"><?= htmlspecialchars((string) ($connection['message'] ?? 'Start the Python service.')) ?></p>
                <?php endif; ?>
                <button class="btn btn-gold" type="submit">Save AI Settings</button>
            </section>
        </form>
        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>
