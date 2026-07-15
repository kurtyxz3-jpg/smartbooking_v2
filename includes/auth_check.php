<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    header('Location: ../auth/login.php');
    exit;
}

function requireRole(string $requiredRole): void
{
    if (($_SESSION['role'] ?? '') !== $requiredRole) {
        http_response_code(403);
        exit('Access denied.');
    }
}
