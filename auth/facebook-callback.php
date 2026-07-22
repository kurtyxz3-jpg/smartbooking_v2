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

    $state = $_GET['state'] ?? '';
    $code = $_GET['code'] ?? '';

    if (
        $state === ''
        || !hash_equals(
            $_SESSION['facebook_oauth_state'] ?? '',
            $state
        )
    ) {
        throw new RuntimeException('Invalid Facebook login state.');
    }

    if ($code === '') {
        throw new RuntimeException('Facebook authorization was cancelled.');
    }

    unset($_SESSION['facebook_oauth_state']);

    $tokenData = oauthHttpGet(
        'https://graph.facebook.com/v20.0/oauth/access_token?'
        . http_build_query([
            'client_id' => FACEBOOK_APP_ID,
            'client_secret' => FACEBOOK_APP_SECRET,
            'redirect_uri' => FACEBOOK_REDIRECT_URI,
            'code' => $code,
        ])
    );

    $accessToken = $tokenData['access_token'] ?? '';

    if ($accessToken === '') {
        throw new RuntimeException(
            'Facebook did not return an access token.'
        );
    }

    $profile = oauthHttpGet(
        'https://graph.facebook.com/me?'
        . http_build_query([
            'fields' => 'id,name,email,first_name,last_name',
            'access_token' => $accessToken,
        ])
    );

    oauthCreateOrLoginCustomer(
        $pdo,
        'facebook',
        (string) ($profile['id'] ?? ''),
        (string) ($profile['email'] ?? ''),
        (string) ($profile['name'] ?? 'Facebook Customer'),
        $profile['first_name'] ?? null,
        $profile['last_name'] ?? null
    );

    header('Location: ../customer/dashboard.php');
    exit;
} catch (Throwable $exception) {
    $_SESSION['social_login_error'] = $exception->getMessage();
    header('Location: register.php?social=failed');
    exit;
}
