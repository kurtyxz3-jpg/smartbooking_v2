<?php
declare(strict_types=1);

function writeAuditLog(
    PDO $pdo,
    string $action,
    string $module,
    ?string $description = null
): void {
    $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    $username = $_SESSION['username'] ?? 'System';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

    $statement = $pdo->prepare(
        "INSERT INTO audit_logs
            (user_id, username, action, module, description, ip_address)
         VALUES
            (:user_id, :username, :action, :module, :description, :ip_address)"
    );

    $statement->execute([
        'user_id' => $userId,
        'username' => $username,
        'action' => $action,
        'module' => $module,
        'description' => $description,
        'ip_address' => $ipAddress,
    ]);
}
