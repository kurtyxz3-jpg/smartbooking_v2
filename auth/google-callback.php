<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../config/oauth.php';

try {
    if (!oauthProviderEnabled('google')) {
        throw new RuntimeException('Google login is not configured.');
    }

    if (isset($_GET['error'])) {
        throw new RuntimeException(
            'Google login was cancelled: '
            . ($_GET['error_description'] ?? $_GET['error'])
        );
    }

    $state = (string) ($_GET['state'] ?? '');
    $code = (string) ($_GET['code'] ?? '');
    $savedState = (string) ($_SESSION['google_oauth_state'] ?? '');

    if (
        $state === ''
        || $savedState === ''
        || !hash_equals($savedState, $state)
    ) {
        throw new RuntimeException(
            'The Google login state did not match. Please try again.'
        );
    }

    if ($code === '') {
        throw new RuntimeException(
            'Google did not return an authorization code.'
        );
    }

    unset(
        $_SESSION['google_oauth_state'],
        $_SESSION['google_oauth_nonce']
    );

    $tokenData = oauthRequest(
        'https://oauth2.googleapis.com/token',
        'POST',
        [
            'code' => $code,
            'client_id' => oauthConfig('google', 'client_id'),
            'client_secret' => oauthConfig('google', 'client_secret'),
            'redirect_uri' => oauthConfig('google', 'redirect_uri'),
            'grant_type' => 'authorization_code',
        ]
    );

    $accessToken = (string) ($tokenData['access_token'] ?? '');

    if ($accessToken === '') {
        throw new RuntimeException(
            'Google did not return an access token.'
        );
    }

    $profile = oauthRequest(
        'https://openidconnect.googleapis.com/v1/userinfo',
        'GET',
        [],
        ['Authorization: Bearer ' . $accessToken]
    );

    if (
        isset($profile['email_verified'])
        && $profile['email_verified'] !== true
    ) {
        throw new RuntimeException(
            'Google has not verified this email address.'
        );
    }

    oauthCreateOrLoginCustomer(
        $pdo,
        'google',
        (string) ($profile['sub'] ?? ''),
        (string) ($profile['email'] ?? ''),
        (string) ($profile['name'] ?? 'Google Customer'),
        isset($profile['given_name'])
            ? (string) $profile['given_name']
            : null,
        isset($profile['family_name'])
            ? (string) $profile['family_name']
            : null
    );

    header('Location: ' . oauthCustomerDestination());
    exit;
} catch (Throwable $exception) {
    $_SESSION['social_login_error'] = $exception->getMessage();
    header('Location: login.php');
    exit;
}
