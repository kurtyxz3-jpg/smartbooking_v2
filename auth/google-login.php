<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/oauth.php';

if (!oauthProviderEnabled('google')) {
    header('Location: register.php?social=google_not_configured');
    exit;
}

$state = bin2hex(random_bytes(24));
$_SESSION['google_oauth_state'] = $state;

$params = [
    'client_id' => GOOGLE_OAUTH_CLIENT_ID,
    'redirect_uri' => GOOGLE_OAUTH_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $state,
    'access_type' => 'online',
    'prompt' => 'select_account',
];

header(
    'Location: https://accounts.google.com/o/oauth2/v2/auth?'
    . http_build_query($params)
);
exit;
