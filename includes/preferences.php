<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

const NBW_ALLOWED_LANGUAGES = ['english', 'filipino', 'taglish'];
const NBW_ALLOWED_THEMES = ['default', 'light', 'dark'];

function nbwNormalizeLanguage(?string $value): string
{
    $value = strtolower(trim((string) $value));

    return in_array($value, NBW_ALLOWED_LANGUAGES, true)
        ? $value
        : 'english';
}

function nbwNormalizeTheme(?string $value): string
{
    $value = strtolower(trim((string) $value));

    return in_array($value, NBW_ALLOWED_THEMES, true)
        ? $value
        : 'default';
}

function nbwSystemPreferences(PDO $pdo): array
{
    $defaults = [
        'language' => 'english',
        'theme' => 'default',
    ];

    try {
        $statement = $pdo->query(
            "SELECT default_language, default_theme
             FROM system_settings
             WHERE id = 1
             LIMIT 1"
        );
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return $defaults;
        }

        return [
            'language' => nbwNormalizeLanguage($row['default_language'] ?? 'english'),
            'theme' => nbwNormalizeTheme($row['default_theme'] ?? 'default'),
        ];
    } catch (Throwable $exception) {
        return $defaults;
    }
}

function nbwUserPreferences(PDO $pdo, int $userId): ?array
{
    if ($userId <= 0) {
        return null;
    }

    try {
        $statement = $pdo->prepare(
            "SELECT language_code, theme_mode
             FROM user_language_preferences
             WHERE user_id = ?
             LIMIT 1"
        );
        $statement->execute([$userId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return [
            'language' => nbwNormalizeLanguage($row['language_code'] ?? 'english'),
            'theme' => nbwNormalizeTheme($row['theme_mode'] ?? 'default'),
        ];
    } catch (Throwable $exception) {
        return null;
    }
}

function nbwResolvedPreferences(PDO $pdo): array
{
    $system = nbwSystemPreferences($pdo);
    $language = $system['language'];
    $theme = $system['theme'];

    /*
     * Browser cookies are available to registered and unregistered visitors.
     * They override the system fallback on the current device.
     */
    if (isset($_COOKIE['nbw_language'])) {
        $language = nbwNormalizeLanguage($_COOKIE['nbw_language']);
    }

    if (isset($_COOKIE['nbw_theme'])) {
        $theme = nbwNormalizeTheme($_COOKIE['nbw_theme']);
    }

    /*
     * A logged-in user's saved account preference has the highest priority.
     */
    if (!empty($_SESSION['user_id'])) {
        $user = nbwUserPreferences($pdo, (int) $_SESSION['user_id']);

        if ($user) {
            $language = $user['language'];
            $theme = $user['theme'];
        }
    }

    return [
        'language' => $language,
        'theme' => $theme,
        'system_language' => $system['language'],
        'system_theme' => $system['theme'],
    ];
}

function nbwSaveUserPreferences(
    PDO $pdo,
    int $userId,
    string $language,
    string $theme
): void {
    if ($userId <= 0) {
        return;
    }

    $statement = $pdo->prepare(
        "INSERT INTO user_language_preferences (
            user_id,
            language_code,
            theme_mode
        ) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE
            language_code = VALUES(language_code),
            theme_mode = VALUES(theme_mode)"
    );
    $statement->execute([
        $userId,
        nbwNormalizeLanguage($language),
        nbwNormalizeTheme($theme),
    ]);
}
