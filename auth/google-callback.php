<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../config/database.php';
require_once '../config/oauth.php';

try {
    if (!oauthProviderEnabled('google')) { throw new RuntimeException('Google login is not configured.'); }
    if (isset($_GET['error'])) { throw new RuntimeException('Google login was cancelled.'); }

    $state = (string) ($_GET['state'] ?? '');
    $code = (string) ($_GET['code'] ?? '');
    $savedState = (string) ($_SESSION['google_oauth_state'] ?? '');
    $intent = (string) ($_SESSION['google_oauth_intent'] ?? 'login');
    if ($state === '' || $savedState === '' || !hash_equals($savedState, $state)) {
        throw new RuntimeException('The Google login state did not match. Please try again.');
    }
    if ($code === '') { throw new RuntimeException('Google did not return an authorization code.'); }
    unset($_SESSION['google_oauth_state'], $_SESSION['google_oauth_nonce'], $_SESSION['google_oauth_intent']);

    $tokenData = oauthRequest('https://oauth2.googleapis.com/token', 'POST', [
        'code' => $code,
        'client_id' => oauthConfig('google', 'client_id'),
        'client_secret' => oauthConfig('google', 'client_secret'),
        'redirect_uri' => oauthConfig('google', 'redirect_uri'),
        'grant_type' => 'authorization_code',
    ]);
    $accessToken = (string) ($tokenData['access_token'] ?? '');
    if ($accessToken === '') { throw new RuntimeException('Google did not return an access token.'); }
    $profile = oauthRequest(
        'https://openidconnect.googleapis.com/v1/userinfo',
        'GET', [], ['Authorization: Bearer ' . $accessToken]
    );
    if (($profile['email_verified'] ?? false) !== true) {
        throw new RuntimeException('Google has not verified this email address.');
    }

    $providerId = trim((string) ($profile['sub'] ?? ''));
    $email = strtolower(trim((string) ($profile['email'] ?? '')));
    $fullname = trim((string) ($profile['name'] ?? 'Google Customer'));
    $firstName = trim((string) ($profile['given_name'] ?? ''));
    $lastName = trim((string) ($profile['family_name'] ?? ''));
    $existing = oauthFindExistingCustomer($pdo, 'google', $providerId, $email);

    if (!$existing) {
        if ($intent !== 'register') {
            throw new RuntimeException('No SmartBooking account is connected to that Google address. Please create an account first.');
        }
        $_SESSION['pending_social_registration'] = [
            'provider' => 'google', 'provider_user_id' => $providerId,
            'email' => $email, 'fullname' => $fullname,
            'first_name' => $firstName, 'last_name' => $lastName,
            'email_verified' => true,
        ];
        $_SESSION['oauth_profile_return_to'] = oauthCustomerDestination();
        header('Location: complete-social-profile.php');
        exit;
    }

    oauthCreateOrLoginCustomer($pdo, 'google', $providerId, $email, $fullname, $firstName, $lastName);
    $check = $pdo->prepare(
        "SELECT NULLIF(TRIM(u.phone_number), '') phone_number,
                NULLIF(TRIM(cp.first_name), '') first_name,
                NULLIF(TRIM(cp.last_name), '') last_name
         FROM users u LEFT JOIN customer_profiles cp ON cp.user_id=u.id
         WHERE u.id=? LIMIT 1"
    );
    $check->execute([(int) $_SESSION['user_id']]);
    $completed = $check->fetch(PDO::FETCH_ASSOC) ?: [];
    if (empty($completed['phone_number']) || empty($completed['first_name']) || empty($completed['last_name'])) {
        $_SESSION['oauth_profile_return_to'] = oauthCustomerDestination();
        header('Location: complete-social-profile.php');
        exit;
    }
    header('Location: ' . oauthCustomerDestination());
    exit;
} catch (Throwable $exception) {
    $_SESSION['social_login_error'] = $exception->getMessage();
    header('Location: login.php');
    exit;
}
