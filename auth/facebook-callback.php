<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../config/database.php';
require_once '../config/oauth.php';

try {
    if (!oauthProviderEnabled('facebook')) { throw new RuntimeException('Facebook login is not configured.'); }
    if (isset($_GET['error'])) { throw new RuntimeException('Facebook login was cancelled.'); }
    $state = (string) ($_GET['state'] ?? '');
    $code = (string) ($_GET['code'] ?? '');
    $savedState = (string) ($_SESSION['facebook_oauth_state'] ?? '');
    $intent = (string) ($_SESSION['facebook_oauth_intent'] ?? 'login');
    if ($state === '' || $savedState === '' || !hash_equals($savedState, $state)) {
        throw new RuntimeException('The Facebook login state did not match. Please try again.');
    }
    if ($code === '') { throw new RuntimeException('Facebook did not return an authorization code.'); }
    unset($_SESSION['facebook_oauth_state'], $_SESSION['facebook_oauth_intent']);

    $version = (string) (oauthConfig('facebook', 'graph_version') ?: 'v25.0');
    $tokenData = oauthRequest(
        'https://graph.facebook.com/' . rawurlencode($version) . '/oauth/access_token',
        'GET', [
            'client_id' => oauthConfig('facebook', 'app_id'),
            'client_secret' => oauthConfig('facebook', 'app_secret'),
            'redirect_uri' => oauthConfig('facebook', 'redirect_uri'),
            'code' => $code,
        ]
    );
    $accessToken = (string) ($tokenData['access_token'] ?? '');
    if ($accessToken === '') { throw new RuntimeException('Facebook did not return an access token.'); }
    $profile = oauthRequest(
        'https://graph.facebook.com/' . rawurlencode($version) . '/me',
        'GET', ['fields' => 'id,name,email,first_name,last_name', 'access_token' => $accessToken]
    );
    $providerId = trim((string) ($profile['id'] ?? ''));
    if ($providerId === '') { throw new RuntimeException('Facebook did not return a usable account identifier.'); }
    $email = strtolower(trim((string) ($profile['email'] ?? '')));
    $hasRealEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    if (!$hasRealEmail) {
        $email = 'facebook.' . substr(hash('sha256', $providerId), 0, 32) . '@facebook.invalid';
    }
    $fullname = trim((string) ($profile['name'] ?? 'Facebook Customer'));
    $firstName = trim((string) ($profile['first_name'] ?? ''));
    $lastName = trim((string) ($profile['last_name'] ?? ''));
    $existing = oauthFindExistingCustomer($pdo, 'facebook', $providerId, $email);

    if (!$existing) {
        if ($intent !== 'register') {
            throw new RuntimeException('No SmartBooking account is connected to that Facebook profile. Please create an account first.');
        }
        $_SESSION['pending_social_registration'] = [
            'provider' => 'facebook', 'provider_user_id' => $providerId,
            'email' => $email, 'fullname' => $fullname,
            'first_name' => $firstName, 'last_name' => $lastName,
            'email_verified' => $hasRealEmail,
        ];
        $_SESSION['facebook_contact_email_missing'] = !$hasRealEmail;
        $_SESSION['oauth_profile_return_to'] = oauthCustomerDestination();
        header('Location: complete-social-profile.php');
        exit;
    }

    oauthCreateOrLoginCustomer($pdo, 'facebook', $providerId, $email, $fullname, $firstName, $lastName);
    $_SESSION['facebook_contact_email_missing'] = !$hasRealEmail;
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
