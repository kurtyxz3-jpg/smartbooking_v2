<?php
declare(strict_types=1);

function adminTableExists(PDO $pdo, string $table): bool
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

function adminSafeCount(PDO $pdo, string $table, string $where = '1=1'): int
{
    if (!adminTableExists($pdo, $table)) {
        return 0;
    }

    return (int) $pdo->query("SELECT COUNT(*) FROM `{$table}` WHERE {$where}")->fetchColumn();
}

function adminFetchRows(
    PDO $pdo,
    string $table,
    string $orderBy = 'id DESC',
    int $limit = 100
): array {
    if (!adminTableExists($pdo, $table)) {
        return [];
    }

    $allowedOrderBy = preg_replace('/[^a-zA-Z0-9_ ,.-]/', '', $orderBy);

    return $pdo->query(
        "SELECT * FROM `{$table}` ORDER BY {$allowedOrderBy} LIMIT {$limit}"
    )->fetchAll();
}
