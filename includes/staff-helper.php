<?php
declare(strict_types=1);

function staffTableExists(PDO $pdo, string $table): bool
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

function staffColumnExists(PDO $pdo, string $table, string $column): bool
{
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

function staffSafeCount(
    PDO $pdo,
    string $table,
    string $where = '1=1',
    array $params = []
): int {
    if (!staffTableExists($pdo, $table)) {
        return 0;
    }

    $statement = $pdo->prepare(
        "SELECT COUNT(*) FROM `{$table}` WHERE {$where}"
    );
    $statement->execute($params);

    return (int) $statement->fetchColumn();
}

function staffStatusClass(string $status): string
{
    return match (strtolower($status)) {
        'confirmed', 'completed', 'active', 'approved' => 'staff-badge-success',
        'cancelled', 'rejected', 'no_show', 'inactive' => 'staff-badge-danger',
        default => 'staff-badge-warning',
    };
}
