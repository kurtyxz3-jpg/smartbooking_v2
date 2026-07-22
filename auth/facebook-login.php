<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/oauth.php';

if (!oauthProviderEnabled('facebook')) {
    header('Location: register.php?social=facebook_not_configured');
    exit;
}

$state = bin2hex(random_bytes(24));
$_SESSION['facebook_oauth_state'] = $state;

$params = [
    'client_id' => FACEBOOK_APP_ID,
    'redirect_uri' => FACEBOOK_REDIRECT_URI,
    'state' => $state,
    'scope' => 'email,public_profile',
    'response_type' => 'code',
];

header(
    'Location: https://www.facebook.com/v20.0/dialog/oauth?'
    . http_build_query($params)
);
exit;
