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

function nbwTableExists(PDO $pdo, string $table): bool
{
    try {
        $statement = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = ?'
        );
        $statement->execute([$table]);
        return (int) $statement->fetchColumn() > 0;
    } catch (Throwable $exception) {
        return false;
    }
}

function nbwTableColumns(PDO $pdo, string $table): array
{
    try {
        $columns = [];
        foreach ($pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll() as $row) {
            $columns[] = (string) $row['Field'];
        }
        return $columns;
    } catch (Throwable $exception) {
        return [];
    }
}

function nbwSystemPreferences(PDO $pdo): array
{
    $defaults = ['language' => 'english', 'theme' => 'default'];

    if (!nbwTableExists($pdo, 'system_settings')) {
        return $defaults;
    }

    try {
        $columns = nbwTableColumns($pdo, 'system_settings');

        if (
            in_array('setting_key', $columns, true)
            && in_array('setting_value', $columns, true)
        ) {
            $statement = $pdo->query(
                "SELECT setting_key, setting_value
                 FROM system_settings
                 WHERE setting_key IN ('default_language', 'default_theme')"
            );
            $values = [];
            foreach ($statement->fetchAll() as $row) {
                $values[(string) $row['setting_key']] =
                    (string) ($row['setting_value'] ?? '');
            }

            return [
                'language' => nbwNormalizeLanguage(
                    $values['default_language'] ?? 'english'
                ),
                'theme' => nbwNormalizeTheme(
                    $values['default_theme'] ?? 'default'
                ),
            ];
        }

        if (
            in_array('default_language', $columns, true)
            && in_array('default_theme', $columns, true)
        ) {
            $row = $pdo->query(
                'SELECT default_language, default_theme
                 FROM system_settings WHERE id = 1 LIMIT 1'
            )->fetch();

            return [
                'language' => nbwNormalizeLanguage(
                    $row['default_language'] ?? 'english'
                ),
                'theme' => nbwNormalizeTheme(
                    $row['default_theme'] ?? 'default'
                ),
            ];
        }
    } catch (Throwable $exception) {
        error_log('System preference load failed: ' . $exception->getMessage());
    }

    return $defaults;
}

function nbwUserPreferences(PDO $pdo, int $userId): ?array
{
    if ($userId <= 0) {
        return null;
    }

    try {
        if (nbwTableExists($pdo, 'user_preferences')) {
            $statement = $pdo->prepare(
                'SELECT language_code, theme_mode
                 FROM user_preferences WHERE user_id = ? LIMIT 1'
            );
            $statement->execute([$userId]);
            $row = $statement->fetch();

            if ($row) {
                return [
                    'language' => nbwNormalizeLanguage(
                        $row['language_code'] ?? 'english'
                    ),
                    'theme' => nbwNormalizeTheme(
                        $row['theme_mode'] ?? 'default'
                    ),
                ];
            }
        }

        if (nbwTableExists($pdo, 'user_language_preferences')) {
            $statement = $pdo->prepare(
                'SELECT language_code, theme_mode
                 FROM user_language_preferences WHERE user_id = ? LIMIT 1'
            );
            $statement->execute([$userId]);
            $row = $statement->fetch();

            if ($row) {
                return [
                    'language' => nbwNormalizeLanguage(
                        $row['language_code'] ?? 'english'
                    ),
                    'theme' => nbwNormalizeTheme(
                        $row['theme_mode'] ?? 'default'
                    ),
                ];
            }
        }
    } catch (Throwable $exception) {
        error_log('User preference load failed: ' . $exception->getMessage());
    }

    return null;
}

function nbwResolvedPreferences(PDO $pdo): array
{
    $system = nbwSystemPreferences($pdo);
    $language = $system['language'];
    $theme = $system['theme'];

    if (isset($_COOKIE['nbw_language'])) {
        $language = nbwNormalizeLanguage($_COOKIE['nbw_language']);
    }
    if (isset($_COOKIE['nbw_theme'])) {
        $theme = nbwNormalizeTheme($_COOKIE['nbw_theme']);
    }

    if (!empty($_SESSION['user_id'])) {
        $saved = nbwUserPreferences($pdo, (int) $_SESSION['user_id']);
        if ($saved) {
            $language = $saved['language'];
            $theme = $saved['theme'];
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

    $language = nbwNormalizeLanguage($language);
    $theme = nbwNormalizeTheme($theme);

    if (nbwTableExists($pdo, 'user_preferences')) {
        $statement = $pdo->prepare(
            'INSERT INTO user_preferences (user_id, language_code, theme_mode)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE
                language_code = VALUES(language_code),
                theme_mode = VALUES(theme_mode)'
        );
        $statement->execute([$userId, $language, $theme]);
        return;
    }

    if (nbwTableExists($pdo, 'user_language_preferences')) {
        $statement = $pdo->prepare(
            'INSERT INTO user_language_preferences
                (user_id, language_code, theme_mode)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE
                language_code = VALUES(language_code),
                theme_mode = VALUES(theme_mode)'
        );
        $statement->execute([$userId, $language, $theme]);
        return;
    }

    throw new RuntimeException('No compatible preference table exists.');
}
