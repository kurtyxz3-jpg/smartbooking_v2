<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$submittedToken = (string) ($_POST['csrf_token'] ?? '');
$storedToken = (string) ($_SESSION['cancel_social_csrf'] ?? '');

if ($storedToken === '' || !hash_equals($storedToken, $submittedToken)) {
    $_SESSION['social_login_error'] = 'Your session expired. Please try again.';
    header('Location: register.php');
    exit;
}

foreach ([
    'pending_social_registration',
    'oauth_profile_return_to',
    'facebook_contact_email_missing',
    'google_oauth_state',
    'google_oauth_nonce',
    'google_oauth_intent',
    'facebook_oauth_state',
    'facebook_oauth_intent',
    'complete_profile_csrf',
    'cancel_social_csrf',
] as $key) {
    unset($_SESSION[$key]);
}

session_regenerate_id(true);

$destination = (string) ($_POST['destination'] ?? 'register');
header('Location: ' . ($destination === 'login' ? 'login.php' : 'register.php'));
exit;
