<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../config/oauth.php';

try {
    if (!oauthProviderEnabled('facebook')) {
        throw new RuntimeException('Facebook login is not configured.');
    }

    if (isset($_GET['error'])) {
        throw new RuntimeException(
            'Facebook login was cancelled: '
            . ($_GET['error_description'] ?? $_GET['error'])
        );
    }

    $state = (string) ($_GET['state'] ?? '');
    $code = (string) ($_GET['code'] ?? '');
    $savedState = (string) ($_SESSION['facebook_oauth_state'] ?? '');

    if (
        $state === ''
        || $savedState === ''
        || !hash_equals($savedState, $state)
    ) {
        throw new RuntimeException(
            'The Facebook login state did not match. Please try again.'
        );
    }

    if ($code === '') {
        throw new RuntimeException(
            'Facebook did not return an authorization code.'
        );
    }

    unset($_SESSION['facebook_oauth_state']);

    $version =
        (string) (oauthConfig('facebook', 'graph_version') ?: 'v25.0');

    $tokenData = oauthRequest(
        'https://graph.facebook.com/'
        . rawurlencode($version)
        . '/oauth/access_token',
        'GET',
        [
            'client_id' => oauthConfig('facebook', 'app_id'),
            'client_secret' => oauthConfig('facebook', 'app_secret'),
            'redirect_uri' => oauthConfig('facebook', 'redirect_uri'),
            'code' => $code,
        ]
    );

    $accessToken = (string) ($tokenData['access_token'] ?? '');

    if ($accessToken === '') {
        throw new RuntimeException(
            'Facebook did not return an access token.'
        );
    }

    $profile = oauthRequest(
        'https://graph.facebook.com/'
        . rawurlencode($version)
        . '/me',
        'GET',
        [
            'fields' => 'id,name,email,first_name,last_name',
            'access_token' => $accessToken,
        ]
    );

    if (empty($profile['email'])) {
        throw new RuntimeException(
            'Facebook did not provide an email address. '
            . 'Use a Facebook account with an email address, '
            . 'or continue with Google.'
        );
    }

    oauthCreateOrLoginCustomer(
        $pdo,
        'facebook',
        (string) ($profile['id'] ?? ''),
        (string) $profile['email'],
        (string) ($profile['name'] ?? 'Facebook Customer'),
        isset($profile['first_name'])
            ? (string) $profile['first_name']
            : null,
        isset($profile['last_name'])
            ? (string) $profile['last_name']
            : null
    );

    header('Location: ' . oauthCustomerDestination());
    exit;
} catch (Throwable $exception) {
    $_SESSION['social_login_error'] = $exception->getMessage();
    header('Location: login.php');
    exit;
}
