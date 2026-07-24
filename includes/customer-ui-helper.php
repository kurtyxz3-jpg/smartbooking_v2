<?php
declare(strict_types=1);

function customerCsrfToken(): string
{
    if (empty($_SESSION['customer_csrf'])) {
        $_SESSION['customer_csrf'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['customer_csrf'];
}

function customerVerifyCsrf(?string $token): bool
{
    return is_string($token)
        && !empty($_SESSION['customer_csrf'])
        && hash_equals((string) $_SESSION['customer_csrf'], $token);
}

function customerTableExists(PDO $pdo, string $table): bool
{
    $statement = $pdo->prepare(
        "SELECT COUNT(*)
         FROM information_schema.tables
         WHERE table_schema = DATABASE()
           AND table_name = ?"
    );
    $statement->execute([$table]);

    return (bool) $statement->fetchColumn();
}

function customerColumnExists(
    PDO $pdo,
    string $table,
    string $column
): bool {
    $statement = $pdo->prepare(
        "SELECT COUNT(*)
         FROM information_schema.columns
         WHERE table_schema = DATABASE()
           AND table_name = ?
           AND column_name = ?"
    );
    $statement->execute([$table, $column]);

    return (bool) $statement->fetchColumn();
}

function customerStatusLabel(string $status): string
{
    return match ($status) {
        'pending' => 'Pending confirmation',
        'confirmed' => 'Confirmed',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'no_show' => 'No show',
        default => ucwords(str_replace('_', ' ', $status)),
    };
}

function customerCanChangeBooking(array $booking): bool
{
    if (!in_array($booking['status'] ?? '', ['pending', 'confirmed'], true)) {
        return false;
    }

    $timestamp = strtotime(
        ($booking['booking_date'] ?? '') . ' '
        . ($booking['booking_time'] ?? '00:00:00')
    );

    return $timestamp !== false && $timestamp > time() + 86400;
}

function customerMoney(float|int|string|null $amount): string
{
    return '₱' . number_format((float) $amount, 2);
}

function customerRedirectWithMessage(
    string $location,
    string $type,
    string $message
): never {
    $_SESSION['customer_flash'] = [
        'type' => $type,
        'message' => $message,
    ];

    header('Location: ' . $location);
    exit;
}

function customerFlash(): ?array
{
    $flash = $_SESSION['customer_flash'] ?? null;
    unset($_SESSION['customer_flash']);

    return is_array($flash) ? $flash : null;
}
