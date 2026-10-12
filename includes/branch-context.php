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

/** Internal compatibility ID, pinned by the non-destructive migration. */
function salonBranchId(PDO $pdo): int
{
    static $id = null;
    if ($id === null) {
        if (!branchTableExists($pdo, 'single_salon_config')) {
            http_response_code(503);
            exit('Salon setup is pending. Please apply database/201_single_salon.sql before using this version.');
        }
        $id = (int) $pdo->query(
            "SELECT c.branch_id FROM single_salon_config c
             JOIN branches b ON b.id = c.branch_id WHERE c.id = 1"
        )->fetchColumn();
        if ($id <= 0) {
            http_response_code(503);
            exit('Salon setup is incomplete. Please contact the system administrator.');
        }
    }
    // Discard old switches and drafts without losing the rest of the booking.
    unset($_SESSION['superadmin_branch_filter']);
    $_SESSION['branch_id'] = $id;
    if (isset($_SESSION['booking_draft']['branch_id'])) {
        $_SESSION['booking_draft']['branch_id'] = $id;
    }
    return $id;
}

function currentUserBranchId(PDO $pdo): ?int
{
    return salonBranchId($pdo);
}

function requireAssignedBranch(PDO $pdo): int
{
    return salonBranchId($pdo);
}

function branchName(PDO $pdo, ?int $branchId): string
{
    return 'Nails & Body Worx';
}

function selectedSuperAdminBranch(PDO $pdo): ?int
{
    return salonBranchId($pdo);
}
