<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/preferences.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'message' => 'Method not allowed.',
    ]);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);

if (!is_array($payload)) {
    $payload = $_POST;
}

$language = nbwNormalizeLanguage($payload['language'] ?? null);
$theme = nbwNormalizeTheme($payload['theme'] ?? null);

$cookieOptions = [
    'expires' => time() + 31536000,
    'path' => '/smartbooking_v2/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => false,
    'samesite' => 'Lax',
];

setcookie('nbw_language', $language, $cookieOptions);
setcookie('nbw_theme', $theme, $cookieOptions);

if (!empty($_SESSION['user_id'])) {
    try {
        nbwSaveUserPreferences(
            $pdo,
            (int) $_SESSION['user_id'],
            $language,
            $theme
        );
    } catch (Throwable $exception) {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'message' => 'The account preference could not be saved.',
        ]);
        exit;
    }
}

echo json_encode([
    'ok' => true,
    'language' => $language,
    'theme' => $theme,
    'reload' => true,
]);
