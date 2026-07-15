<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
require_once '../includes/audit-helper.php';

$pageTitle = 'AI Settings';
$message = '';
$error = '';

$defaults = [
    'nail_generator_enabled' => 1,
    'chatbot_enabled' => 1,
    'receipt_verification_enabled' => 1,
    'style_recommendation_enabled' => 1,
    'daily_generation_limit' => 10,
    'ai_service_url' => 'http://127.0.0.1:5000',
    'api_key' => '',
];

$statement = $pdo->query("SELECT * FROM ai_settings WHERE id = 1 LIMIT 1");
$settings = $statement->fetch() ?: $defaults;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nailGeneratorEnabled = isset($_POST['nail_generator_enabled']) ? 1 : 0;
    $chatbotEnabled = isset($_POST['chatbot_enabled']) ? 1 : 0;
    $receiptVerificationEnabled = isset($_POST['receipt_verification_enabled']) ? 1 : 0;
    $styleRecommendationEnabled = isset($_POST['style_recommendation_enabled']) ? 1 : 0;
    $dailyGenerationLimit = filter_input(INPUT_POST, 'daily_generation_limit', FILTER_VALIDATE_INT);
    $aiServiceUrl = trim($_POST['ai_service_url'] ?? '');
    $apiKey = trim($_POST['api_key'] ?? '');

    if ($dailyGenerationLimit === false || $dailyGenerationLimit < 1 || $dailyGenerationLimit > 500) {
        $error = 'Daily generation limit must be between 1 and 500.';
    } elseif ($aiServiceUrl !== '' && !filter_var($aiServiceUrl, FILTER_VALIDATE_URL)) {
        $error = 'Please enter a valid AI service URL.';
    } else {
        $statement = $pdo->prepare(
            "INSERT INTO ai_settings (
                id,
                nail_generator_enabled,
                chatbot_enabled,
                receipt_verification_enabled,
                style_recommendation_enabled,
                daily_generation_limit,
                ai_service_url,
                api_key
            ) VALUES (
                1,
                :nail_generator_enabled,
                :chatbot_enabled,
                :receipt_verification_enabled,
                :style_recommendation_enabled,
                :daily_generation_limit,
                :ai_service_url,
                :api_key
            )
            ON DUPLICATE KEY UPDATE
                nail_generator_enabled = VALUES(nail_generator_enabled),
                chatbot_enabled = VALUES(chatbot_enabled),
                receipt_verification_enabled = VALUES(receipt_verification_enabled),
                style_recommendation_enabled = VALUES(style_recommendation_enabled),
                daily_generation_limit = VALUES(daily_generation_limit),
                ai_service_url = VALUES(ai_service_url),
                api_key = VALUES(api_key)"
        );

        $statement->execute([
            'nail_generator_enabled' => $nailGeneratorEnabled,
            'chatbot_enabled' => $chatbotEnabled,
            'receipt_verification_enabled' => $receiptVerificationEnabled,
            'style_recommendation_enabled' => $styleRecommendationEnabled,
            'daily_generation_limit' => $dailyGenerationLimit,
            'ai_service_url' => $aiServiceUrl,
            'api_key' => $apiKey,
        ]);

        writeAuditLog(
            $pdo,
            'Updated AI settings',
            'AI Settings',
            'AI feature availability and connection settings were updated.'
        );

        $message = 'AI settings saved successfully.';

        $statement = $pdo->query("SELECT * FROM ai_settings WHERE id = 1 LIMIT 1");
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
                <h1 class="gold-title">AI Settings</h1>
                <p>Enable AI modules and configure the Python AI service connection.</p>
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
                    <h3>AI Module Availability</h3>

                    <div class="toggle-grid">
                        <label class="toggle-card">
                            <input type="checkbox" name="nail_generator_enabled"
                                <?= !empty($settings['nail_generator_enabled']) ? 'checked' : '' ?>>
                            <span>
                                <strong>AI Nail Art Generator</strong>
                                <small>Allow customers to generate nail design concepts.</small>
                            </span>
                        </label>

                        <label class="toggle-card">
                            <input type="checkbox" name="chatbot_enabled"
                                <?= !empty($settings['chatbot_enabled']) ? 'checked' : '' ?>>
                            <span>
                                <strong>FAQ Chatbot</strong>
                                <small>Provide automated responses to common customer questions.</small>
                            </span>
                        </label>

                        <label class="toggle-card">
                            <input type="checkbox" name="receipt_verification_enabled"
                                <?= !empty($settings['receipt_verification_enabled']) ? 'checked' : '' ?>>
                            <span>
                                <strong>Receipt Verification</strong>
                                <small>Analyze uploaded payment receipts before booking confirmation.</small>
                            </span>
                        </label>

                        <label class="toggle-card">
                            <input type="checkbox" name="style_recommendation_enabled"
                                <?= !empty($settings['style_recommendation_enabled']) ? 'checked' : '' ?>>
                            <span>
                                <strong>Style Recommendation</strong>
                                <small>Suggest suitable styles based on customer preferences.</small>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>AI Service Connection</h3>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="daily_generation_limit">Daily Generation Limit per Customer</label>
                            <input id="daily_generation_limit" name="daily_generation_limit"
                                   type="number" min="1" max="500"
                                   value="<?= (int) ($settings['daily_generation_limit'] ?? 10) ?>">
                        </div>

                        <div class="form-group">
                            <label for="ai_service_url">Python AI Service URL</label>
                            <input id="ai_service_url" name="ai_service_url" type="url"
                                   value="<?= htmlspecialchars($settings['ai_service_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="form-group form-span-2">
                            <label for="api_key">AI API Key</label>
                            <input id="api_key" name="api_key" type="password"
                                   value="<?= htmlspecialchars($settings['api_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                   autocomplete="off">
                            <small class="field-note">For local development, this may remain blank until the AI service is configured.</small>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-gold" type="submit">Save AI Settings</button>
                </div>
            </form>
        </section>

        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>
