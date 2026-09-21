<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/oauth.php';

if (!oauthProviderEnabled('facebook')) {
    $_SESSION['social_login_error'] =
        'Facebook login needs an App ID and App Secret.';
    header('Location: login.php');
    exit;
}

$state = bin2hex(random_bytes(32));
$intent = (string) ($_GET['intent'] ?? 'login');
if (!in_array($intent, ['login', 'register'], true)) {
    $intent = 'login';
}
$_SESSION['facebook_oauth_state'] = $state;
$_SESSION['facebook_oauth_intent'] = $intent;

$version =
    (string) (oauthConfig('facebook', 'graph_version') ?: 'v25.0');

$params = [
    'client_id' => oauthConfig('facebook', 'app_id'),
    'redirect_uri' => oauthConfig('facebook', 'redirect_uri'),
    'state' => $state,
    'scope' => 'email,public_profile',
    'auth_type' => 'rerequest',
    'return_scopes' => 'true',
    'response_type' => 'code',
];

header(
    'Location: https://www.facebook.com/'
    . rawurlencode($version)
    . '/dialog/oauth?'
    . http_build_query($params)
);
exit;
