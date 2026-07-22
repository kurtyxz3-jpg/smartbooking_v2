<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
require_once '../includes/language.php';

$pageTitle = t('language_settings');
$message = '';
$error = '';

$allowedLanguages = ['english', 'filipino', 'taglish'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $language = $_POST['language'] ?? 'english';

    if (!in_array($language, $allowedLanguages, true)) {
        $error = t('invalid_language', 'Invalid language selection.');
    } else {
        $statement = $pdo->prepare(
            "INSERT INTO localization_settings (id, language_code)
             VALUES (1, ?)
             ON DUPLICATE KEY UPDATE language_code = VALUES(language_code)"
        );
        $statement->execute([$language]);

        $statement = $pdo->prepare(
            "INSERT INTO user_language_preferences (user_id, language_code)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE language_code = VALUES(language_code)"
        );
        $statement->execute([
            (int) $_SESSION['user_id'],
            $language,
        ]);

        $message = t('language_saved', 'Language settings saved successfully.');
        $languageCode = $language;
        $lang = require '../languages/' . $language . '.php';
    }
}

$currentLanguage = 'english';

try {
    $statement = $pdo->query(
        "SELECT language_code
         FROM localization_settings
         WHERE id = 1
         LIMIT 1"
    );
    $savedLanguage = $statement->fetchColumn();

    if (in_array($savedLanguage, $allowedLanguages, true)) {
        $currentLanguage = $savedLanguage;
    }
} catch (Throwable $exception) {
    $currentLanguage = 'english';
}

include '../includes/superadmin-header.php';
?>

<div class="dashboard">
    <?php include '../includes/superadmin-sidebar.php'; ?>

    <main class="content">
        <div class="page-header">
            <div>
                <h1 class="gold-title"><?= htmlspecialchars(t('language_settings'), ENT_QUOTES, 'UTF-8') ?></h1>
                <p>Select the default interface language for SmartBooking V2.</p>
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

        <section class="card language-card">
            <form method="POST">
                <div class="language-options">
                    <label class="language-option">
                        <input
                            type="radio"
                            name="language"
                            value="english"
                            <?= $currentLanguage === 'english' ? 'checked' : '' ?>
                        >
                        <span>
                            <strong>English</strong>
                            <small>Recommended for technical and formal system use.</small>
                        </span>
                    </label>

                    <label class="language-option">
                        <input
                            type="radio"
                            name="language"
                            value="filipino"
                            <?= $currentLanguage === 'filipino' ? 'checked' : '' ?>
                        >
                        <span>
                            <strong>Filipino</strong>
                            <small>Uses Filipino translations for common interface labels.</small>
                        </span>
                    </label>

                    <label class="language-option">
                        <input
                            type="radio"
                            name="language"
                            value="taglish"
                            <?= $currentLanguage === 'taglish' ? 'checked' : '' ?>
                        >
                        <span>
                            <strong>Taglish</strong>
                            <small>Uses natural Filipino-English wording for daily salon use.</small>
                        </span>
                    </label>
                </div>

                <div class="form-actions">
                    <button class="btn btn-gold" type="submit">
                        <?= htmlspecialchars(t('save_changes'), ENT_QUOTES, 'UTF-8') ?>
                    </button>
                </div>
            </form>
        </section>

        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>
