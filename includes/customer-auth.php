<?php
declare(strict_types=1);

require_once __DIR__ . '/auth_check.php';
requireRole('customer');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/registration-security.php';

$authenticatedCustomerId = (int) ($_SESSION['user_id'] ?? 0);
if (
    $authenticatedCustomerId > 0
    && !registrationSecurityManualEmailVerified($pdo, $authenticatedCustomerId)
) {
    registrationSecurityRequirePendingVerification($pdo, $authenticatedCustomerId);
    header('Location: ../auth/verification-pending.php');
    exit;
}

if (
    $authenticatedCustomerId > 0
    && !registrationSecurityCustomerProfileComplete($pdo, $authenticatedCustomerId)
) {
    $profile = registrationSecurityCustomerProfile($pdo, $authenticatedCustomerId);
    $method = strtolower((string) ($profile['registration_method'] ?? ''));
    if (in_array($method, ['google', 'facebook'], true)) {
        $_SESSION['social_provider'] = $method;
        header('Location: ../auth/complete-social-profile.php');
        exit;
    }
}
