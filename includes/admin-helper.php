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

function adminSafeCount(
    PDO $pdo,
    string $table,
    string $where = '1=1',
    array $params = []
): int {
    if (!adminTableExists($pdo, $table)) {
        return 0;
    }

    // Table names cannot be bound as PDO parameters. Restrict the value to a
    // simple SQL identifier before placing it in the query.
    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        throw new InvalidArgumentException('Invalid table name.');
    }

    $statement = $pdo->prepare(
        "SELECT COUNT(*) FROM `{$table}` WHERE {$where}"
    );
    $statement->execute($params);

    return (int) $statement->fetchColumn();
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

    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        throw new InvalidArgumentException('Invalid table name.');
    }

    $allowedOrderBy = preg_replace('/[^a-zA-Z0-9_ ,.-]/', '', $orderBy);
    $safeLimit = max(1, min($limit, 500));

    return $pdo->query(
        "SELECT * FROM `{$table}`
         ORDER BY {$allowedOrderBy}
         LIMIT {$safeLimit}"
    )->fetchAll(PDO::FETCH_ASSOC);
}
