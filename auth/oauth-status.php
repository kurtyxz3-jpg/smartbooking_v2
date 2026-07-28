<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/oauth.php';
require_once '../includes/language.php';

$googleReady = oauthProviderEnabled('google');
$facebookReady = oauthProviderEnabled('facebook');
$cURLReady = function_exists('curl_init');
$openSSLReady = extension_loaded('openssl');

function statusBadge(bool $ready): string
{
    return $ready
        ? '<span class="ready">Ready</span>'
        : '<span class="missing">Not ready</span>';
}
?>
<!doctype html>
<html
    lang="<?= htmlspecialchars($languageCode, ENT_QUOTES, 'UTF-8') ?>"
    data-theme="<?= htmlspecialchars($themeMode, ENT_QUOTES, 'UTF-8') ?>"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Social Login Check | SmartBooking</title>
    <script src="../assets/js/theme-bootstrap.js"></script>
    <link rel="stylesheet" href="../assets/css/global-theme-system.css?v=083">
    <style>
        *{box-sizing:border-box}
        body{margin:0;padding:40px 18px;background:var(--theme-page);color:var(--theme-text);font-family:Arial,sans-serif}
        main{max-width:850px;margin:auto;padding:30px;border:1px solid var(--theme-border);border-radius:24px;background:var(--theme-surface);box-shadow:var(--theme-shadow)}
        h1{margin-top:0;font-family:Georgia,serif;font-size:2.7rem}
        .row{padding:16px 0;display:flex;justify-content:space-between;gap:20px;border-bottom:1px solid var(--theme-border)}
        .ready,.missing{padding:6px 10px;border-radius:999px;font-size:.75rem;font-weight:700}
        .ready{color:var(--theme-success);background:var(--theme-success-soft)}
        .missing{color:var(--theme-danger);background:var(--theme-danger-soft)}
        code{display:block;margin-top:8px;padding:10px;border:1px solid var(--theme-border);border-radius:9px;background:var(--theme-surface-soft);word-break:break-all}
        a{color:var(--theme-link);font-weight:700}
    </style>
</head>
<body>
<main>
    <h1>Social Login Readiness</h1>

    <div class="row"><strong>PHP cURL</strong><?= statusBadge($cURLReady) ?></div>
    <div class="row"><strong>PHP OpenSSL</strong><?= statusBadge($openSSLReady) ?></div>
    <div class="row"><strong>Google credentials</strong><?= statusBadge($googleReady) ?></div>
    <div class="row"><strong>Facebook credentials</strong><?= statusBadge($facebookReady) ?></div>

    <h2>Google redirect URI</h2>
    <code><?= htmlspecialchars((string) oauthConfig('google', 'redirect_uri')) ?></code>

    <h2>Facebook redirect URI</h2>
    <code><?= htmlspecialchars((string) oauthConfig('facebook', 'redirect_uri')) ?></code>

    <p>
        Edit <code>C:\xampp\htdocs\smartbooking_v2\config\oauth-credentials.php</code>
        and refresh this page.
    </p>

    <p><a href="login.php">Return to sign in</a></p>
</main>
</body>
</html>
