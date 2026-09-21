<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/oauth.php';

if (!oauthProviderEnabled('google')) {
    $_SESSION['social_login_error'] =
        'Google login needs a Client ID and Client Secret.';
    header('Location: login.php');
    exit;
}

$state = bin2hex(random_bytes(32));
$nonce = bin2hex(random_bytes(32));
$intent = (string) ($_GET['intent'] ?? 'login');
if (!in_array($intent, ['login', 'register'], true)) {
    $intent = 'login';
}

$_SESSION['google_oauth_state'] = $state;
$_SESSION['google_oauth_nonce'] = $nonce;
$_SESSION['google_oauth_intent'] = $intent;

$params = [
    'client_id' => oauthConfig('google', 'client_id'),
    'redirect_uri' => oauthConfig('google', 'redirect_uri'),
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $state,
    'nonce' => $nonce,
    'access_type' => 'online',
    'include_granted_scopes' => 'true',
    'prompt' => 'select_account',
];

header(
    'Location: https://accounts.google.com/o/oauth2/v2/auth?'
    . http_build_query($params)
);
exit;
