<?php
declare(strict_types=1);

function branchColumnExists(PDO $pdo, string $table, string $column): bool
{
    static $cache = [];
    $key = $table . '.' . $column;
    if (!array_key_exists($key, $cache)) {
        $statement = $pdo->prepare(
            "SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND column_name = ?"
        );
        $statement->execute([$table, $column]);
        $cache[$key] = (bool) $statement->fetchColumn();
    }
    return $cache[$key];
}

function branchTableExists(PDO $pdo, string $table): bool
{
    $statement = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name = ?"
    );
    $statement->execute([$table]);
    return (bool) $statement->fetchColumn();
}

function activeBranches(PDO $pdo): array
{
    if (!branchTableExists($pdo, 'branches')) {
        return [];
    }
    return $pdo->query(
        "SELECT id, branch_code, branch_name, address, contact_number,
                email, opening_time, closing_time, status
         FROM branches
         WHERE status = 'active'
         ORDER BY display_order, branch_name"
    )->fetchAll(PDO::FETCH_ASSOC);
}

function currentUserBranchId(PDO $pdo): ?int
{
    if (isset($_SESSION['branch_id'])) {
        $value = (int) $_SESSION['branch_id'];
        return $value > 0 ? $value : null;
    }
    if (empty($_SESSION['user_id']) || !branchColumnExists($pdo, 'users', 'branch_id')) {
        return null;
    }
    $statement = $pdo->prepare("SELECT branch_id FROM users WHERE id = ? LIMIT 1");
    $statement->execute([(int) $_SESSION['user_id']]);
    $value = (int) $statement->fetchColumn();
    $_SESSION['branch_id'] = $value > 0 ? $value : null;
    return $value > 0 ? $value : null;
}

function requireAssignedBranch(PDO $pdo): int
{
    $branchId = currentUserBranchId($pdo);
    if (!$branchId) {
        http_response_code(403);
        exit('Your account has no active branch assignment. Please contact the Super Administrator.');
    }
    return $branchId;
}

function branchName(PDO $pdo, ?int $branchId): string
{
    if (!$branchId || !branchTableExists($pdo, 'branches')) {
        return 'Unassigned branch';
    }
    $statement = $pdo->prepare("SELECT branch_name FROM branches WHERE id = ? LIMIT 1");
    $statement->execute([$branchId]);
    return (string) ($statement->fetchColumn() ?: 'Unknown branch');
}

function selectedSuperAdminBranch(PDO $pdo): ?int
{
    if (($_SESSION['role'] ?? '') !== 'super_admin') {
        return currentUserBranchId($pdo);
    }
    $requested = filter_input(INPUT_GET, 'branch_id', FILTER_VALIDATE_INT);
    if ($requested) {
        $_SESSION['superadmin_branch_filter'] = $requested;
    } elseif (isset($_GET['branch_id']) && $_GET['branch_id'] === '') {
        unset($_SESSION['superadmin_branch_filter']);
    }
    return isset($_SESSION['superadmin_branch_filter'])
        ? (int) $_SESSION['superadmin_branch_filter']
        : null;
}

