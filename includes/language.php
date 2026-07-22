<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$allowedLanguages = ['english', 'filipino', 'taglish'];
$languageCode = 'english';

try {
    if (!empty($_SESSION['user_id'])) {
        $statement = $pdo->prepare(
            "SELECT language_code
             FROM user_language_preferences
             WHERE user_id = ?
             LIMIT 1"
        );
        $statement->execute([(int) $_SESSION['user_id']]);
        $savedLanguage = $statement->fetchColumn();

        if (in_array($savedLanguage, $allowedLanguages, true)) {
            $languageCode = $savedLanguage;
        }
    }

    if ($languageCode === 'english') {
        $statement = $pdo->query(
            "SELECT language_code
             FROM localization_settings
             WHERE id = 1
             LIMIT 1"
        );
        $systemLanguage = $statement->fetchColumn();

        if (in_array($systemLanguage, $allowedLanguages, true)) {
            $languageCode = $systemLanguage;
        }
    }
} catch (Throwable $exception) {
    $languageCode = 'english';
}

$languageFile = __DIR__ . '/../languages/' . $languageCode . '.php';

if (!is_file($languageFile)) {
    $languageFile = __DIR__ . '/../languages/english.php';
    $languageCode = 'english';
}

$lang = require $languageFile;

function t(string $key, ?string $fallback = null): string
{
    global $lang;

    return $lang[$key] ?? $fallback ?? $key;
}

function translateRenderedInterface(string $html): string
{
    global $lang, $languageCode;

    if ($languageCode === 'english') {
        return $html;
    }

    $phrases = $lang['phrases'] ?? [];

    if (!$phrases) {
        return $html;
    }

    // Longest phrases first prevents a short label from changing part of a longer phrase.
    uksort(
        $phrases,
        static fn(string $left, string $right): int => strlen($right) <=> strlen($left)
    );

    return strtr($html, $phrases);
}

function startInterfaceTranslation(): void
{
    static $started = false;

    if ($started) {
        return;
    }

    $started = true;
    ob_start('translateRenderedInterface');
}
