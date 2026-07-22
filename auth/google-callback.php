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

    $state = $_GET['state'] ?? '';
    $code = $_GET['code'] ?? '';

    if (
        $state === ''
        || !hash_equals(
            $_SESSION['google_oauth_state'] ?? '',
            $state
        )
    ) {
        throw new RuntimeException('Invalid Google login state.');
    }

    if ($code === '') {
        throw new RuntimeException('Google authorization was cancelled.');
    }

    unset($_SESSION['google_oauth_state']);

    $tokenUrl = 'https://oauth2.googleapis.com/token?'
        . http_build_query([
            'code' => $code,
            'client_id' => GOOGLE_OAUTH_CLIENT_ID,
            'client_secret' => GOOGLE_OAUTH_CLIENT_SECRET,
            'redirect_uri' => GOOGLE_OAUTH_REDIRECT_URI,
            'grant_type' => 'authorization_code',
        ]);

    $tokenData = oauthHttpGet($tokenUrl);
    $accessToken = $tokenData['access_token'] ?? '';

    if ($accessToken === '') {
        throw new RuntimeException(
            'Google did not return an access token.'
        );
    }

    $profile = oauthHttpGet(
        'https://openidconnect.googleapis.com/v1/userinfo?'
        . http_build_query(['access_token' => $accessToken])
    );

    oauthCreateOrLoginCustomer(
        $pdo,
        'google',
        (string) ($profile['sub'] ?? ''),
        (string) ($profile['email'] ?? ''),
        (string) ($profile['name'] ?? 'Google Customer'),
        $profile['given_name'] ?? null,
        $profile['family_name'] ?? null
    );

    header('Location: ../customer/dashboard.php');
    exit;
} catch (Throwable $exception) {
    $_SESSION['social_login_error'] = $exception->getMessage();
    header('Location: register.php?social=failed');
    exit;
}
