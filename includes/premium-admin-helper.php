<?php
declare(strict_types=1);

function premiumAdminCsrf(): string
{
    if (empty($_SESSION['premium_admin_csrf'])) {
        $_SESSION['premium_admin_csrf'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['premium_admin_csrf'];
}

function premiumAdminVerifyCsrf(?string $token): bool
{
    return is_string($token)
        && !empty($_SESSION['premium_admin_csrf'])
        && hash_equals((string) $_SESSION['premium_admin_csrf'], $token);
}

function premiumAdminFlash(string $type, string $message, string $page): never
{
    $_SESSION['premium_admin_flash'] = [
        'type' => $type,
        'message' => $message,
    ];

    header('Location: ' . $page);
    exit;
}

function premiumAdminGetFlash(): ?array
{
    $flash = $_SESSION['premium_admin_flash'] ?? null;
    unset($_SESSION['premium_admin_flash']);

    return is_array($flash) ? $flash : null;
}

function premiumAdminMoney(float|int|string|null $amount): string
{
    return '₱' . number_format((float) $amount, 2);
}

function premiumAdminStatus(string $status): string
{
    return ucwords(str_replace('_', ' ', $status));
}

function premiumAdminBind(
    PDOStatement $statement,
    array $params
): void {
    foreach ($params as $name => $value) {
        $statement->bindValue(
            ':' . $name,
            $value,
            is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
        );
    }
}

function premiumAdminColumnExists(
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

function premiumAdminDateRange(): array
{
    $from = trim((string) ($_GET['from'] ?? date('Y-m-01')));
    $to = trim((string) ($_GET['to'] ?? date('Y-m-t')));

    $fromDate = DateTime::createFromFormat('Y-m-d', $from);
    $toDate = DateTime::createFromFormat('Y-m-d', $to);

    if (!$fromDate || $fromDate->format('Y-m-d') !== $from) {
        $from = date('Y-m-01');
    }

    if (!$toDate || $toDate->format('Y-m-d') !== $to) {
        $to = date('Y-m-t');
    }

    if ($from > $to) {
        [$from, $to] = [$to, $from];
    }

    return [$from, $to];
}
