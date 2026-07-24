<?php
declare(strict_types=1);

require_once __DIR__ . '/preferences.php';

$resolvedPreferences = nbwResolvedPreferences($pdo);
$languageCode = $resolvedPreferences['language'];
$themeMode = $resolvedPreferences['theme'];

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

    uksort(
        $phrases,
        static fn(string $left, string $right): int =>
            mb_strlen($right) <=> mb_strlen($left)
    );

    /*
     * Translate only visible text nodes between HTML tags.
     * This avoids changing URLs, CSS classes, form values, and data attributes.
     */
    return preg_replace_callback(
        '/>([^<]+)</u',
        static function (array $match) use ($phrases): string {
            $text = $match[1];

            if (trim($text) === '') {
                return $match[0];
            }

            $translated = strtr($text, $phrases);

            return '>' . $translated . '<';
        },
        $html
    ) ?? $html;
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
