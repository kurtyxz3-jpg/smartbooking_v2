<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
require_once '../includes/audit-helper.php';

$pageTitle = 'Backup & Restore';
$message = '';
$error = '';

function quoteSqlValue(PDO $pdo, mixed $value): string
{
    if ($value === null) {
        return 'NULL';
    }

    return $pdo->quote((string) $value);
}

function splitSqlStatements(string $sql): array
{
    $statements = [];
    $current = '';
    $inSingleQuote = false;
    $inDoubleQuote = false;
    $escaped = false;

    $length = strlen($sql);

    for ($i = 0; $i < $length; $i++) {
        $character = $sql[$i];

        if ($escaped) {
            $current .= $character;
            $escaped = false;
            continue;
        }

        if ($character === '\\') {
            $current .= $character;
            $escaped = true;
            continue;
        }

        if ($character === "'" && !$inDoubleQuote) {
            $inSingleQuote = !$inSingleQuote;
            $current .= $character;
            continue;
        }

        if ($character === '"' && !$inSingleQuote) {
            $inDoubleQuote = !$inDoubleQuote;
            $current .= $character;
            continue;
        }

        if ($character === ';' && !$inSingleQuote && !$inDoubleQuote) {
            if (trim($current) !== '') {
                $statements[] = trim($current);
            }

            $current = '';
            continue;
        }

        $current .= $character;
    }

    if (trim($current) !== '') {
        $statements[] = trim($current);
    }

    return $statements;
}

if (isset($_GET['action']) && $_GET['action'] === 'download') {
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    $backup = "-- SmartBooking Version 2 Database Backup\n";
    $backup .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    $backup .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    foreach ($tables as $table) {
        $createStatement = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
        $createSql = $createStatement['Create Table'] ?? $createStatement[1] ?? '';

        $backup .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $backup .= $createSql . ";\n\n";

        $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $columns = array_map(
                static fn(string $column): string => "`{$column}`",
                array_keys($row)
            );

            $values = array_map(
                static fn(mixed $value): string => quoteSqlValue($pdo, $value),
                array_values($row)
            );

            $backup .= "INSERT INTO `{$table}` (" . implode(', ', $columns) . ") VALUES (";
            $backup .= implode(', ', $values) . ");\n";
        }

        $backup .= "\n";
    }

    $backup .= "SET FOREIGN_KEY_CHECKS = 1;\n";

    writeAuditLog(
        $pdo,
        'Created database backup',
        'Backup & Restore',
        'A full SQL backup was downloaded.'
    );

    $filename = 'smartbooking_v2_backup_' . date('Ymd_His') . '.sql';

    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($backup));

    echo $backup;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_backup'])) {
    if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a valid SQL backup file.';
    } else {
        $extension = strtolower(pathinfo($_FILES['backup_file']['name'], PATHINFO_EXTENSION));

        if ($extension !== 'sql') {
            $error = 'Only SQL backup files are allowed.';
        } else {
            $sql = file_get_contents($_FILES['backup_file']['tmp_name']);

            if ($sql === false || trim($sql) === '') {
                $error = 'The selected backup file is empty or unreadable.';
            } else {
                try {
                    $pdo->beginTransaction();

                    foreach (splitSqlStatements($sql) as $statement) {
                        if ($statement !== '') {
                            $pdo->exec($statement);
                        }
                    }

                    $pdo->commit();

                    writeAuditLog(
                        $pdo,
                        'Restored database backup',
                        'Backup & Restore',
                        'A database backup file was restored.'
                    );

                    $message = 'Database backup restored successfully.';
                } catch (Throwable $exception) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }

                    $error = 'Restore failed: ' . $exception->getMessage();
                }
            }
        }
    }
}

include '../includes/superadmin-header.php';
?>

<div class="dashboard">
    <?php include '../includes/superadmin-sidebar.php'; ?>

    <main class="content">
        <div class="page-header">
            <div>
                <h1 class="gold-title">Backup &amp; Restore</h1>
                <p>Create a full SQL backup or restore the database from a previous backup.</p>
            </div>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="backup-grid">
            <section class="card backup-card">
                <h3>Create Backup</h3>
                <p>Download a complete SQL copy of the current SmartBooking database.</p>

                <a class="btn btn-gold" href="backup.php?action=download">
                    Download Database Backup
                </a>
            </section>

            <section class="card backup-card">
                <h3>Restore Backup</h3>
                <p>Restore the system using a SQL file previously generated by this module.</p>

                <form method="POST" enctype="multipart/form-data"
                      onsubmit="return confirm('Restoring a backup may replace current data. Continue?');">
                    <div class="form-group">
                        <label for="backup_file">SQL Backup File</label>
                        <input id="backup_file" name="backup_file" type="file" accept=".sql" required>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-danger" type="submit" name="restore_backup">
                            Restore Database
                        </button>
                    </div>
                </form>
            </section>
        </div>

        <section class="card backup-notice">
            <h3>Important</h3>
            <p>
                Always create a fresh backup before restoring an older database.
                Restore files should come only from a trusted SmartBooking backup.
            </p>
        </section>

        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>
