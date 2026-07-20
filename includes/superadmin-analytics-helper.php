<?php
declare(strict_types=1);

function saTableExists(PDO $pdo, string $table): bool
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

function saColumnExists(PDO $pdo, string $table, string $column): bool
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

function saSafeCount(
    PDO $pdo,
    string $table,
    string $where = '1=1',
    array $params = []
): int {
    if (!saTableExists($pdo, $table)) {
        return 0;
    }

    $statement = $pdo->prepare(
        "SELECT COUNT(*) FROM `{$table}` WHERE {$where}"
    );
    $statement->execute($params);

    return (int) $statement->fetchColumn();
}

function saSafeSum(
    PDO $pdo,
    string $table,
    string $column,
    string $where = '1=1',
    array $params = []
): float {
    if (!saTableExists($pdo, $table) || !saColumnExists($pdo, $table, $column)) {
        return 0.0;
    }

    $statement = $pdo->prepare(
        "SELECT COALESCE(SUM(`{$column}`), 0)
         FROM `{$table}`
         WHERE {$where}"
    );
    $statement->execute($params);

    return (float) $statement->fetchColumn();
}

function saMoney(float $amount): string
{
    return '₱' . number_format($amount, 2);
}

function saDateRange(): array
{
    $startDate = $_GET['start_date'] ?? date('Y-m-01');
    $endDate = $_GET['end_date'] ?? date('Y-m-d');

    $startObject = DateTime::createFromFormat('Y-m-d', $startDate);
    $endObject = DateTime::createFromFormat('Y-m-d', $endDate);

    if (!$startObject || $startObject->format('Y-m-d') !== $startDate) {
        $startDate = date('Y-m-01');
    }

    if (!$endObject || $endObject->format('Y-m-d') !== $endDate) {
        $endDate = date('Y-m-d');
    }

    if ($startDate > $endDate) {
        [$startDate, $endDate] = [$endDate, $startDate];
    }

    return [$startDate, $endDate];
}

function saPercentChange(float $current, float $previous): ?float
{
    if ($previous == 0.0) {
        return $current > 0 ? 100.0 : null;
    }

    return (($current - $previous) / $previous) * 100;
}
