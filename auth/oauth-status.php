<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/oauth.php';

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
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Social Login Check | SmartBooking</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;padding:40px 18px;background:#f7f1e9;color:#241713;font-family:Arial,sans-serif}
        main{max-width:850px;margin:auto;padding:30px;border-radius:24px;background:#fff;box-shadow:0 20px 60px rgba(40,20,12,.12)}
        h1{margin-top:0;font-family:Georgia,serif;font-size:2.7rem}
        .row{padding:16px 0;display:flex;justify-content:space-between;gap:20px;border-bottom:1px solid #e5ddd7}
        .ready,.missing{padding:6px 10px;border-radius:999px;font-size:.75rem;font-weight:700}
        .ready{color:#205833;background:#def2e5}
        .missing{color:#7c331f;background:#f8dfd8}
        code{display:block;margin-top:8px;padding:10px;border-radius:9px;background:#f4efe9;word-break:break-all}
        a{color:#805619;font-weight:700}
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
