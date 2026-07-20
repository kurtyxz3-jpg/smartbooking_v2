<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';

$pageTitle = 'Audit Logs';

$search = trim($_GET['search'] ?? '');
$module = trim($_GET['module'] ?? '');

$sql = "SELECT id, username, action, module, description, ip_address, created_at
        FROM audit_logs
        WHERE 1 = 1";

$params = [];

if ($search !== '') {
    $sql .= " AND (
        username LIKE :search_username
        OR action LIKE :search_action
        OR description LIKE :search_description
    )";

    $term = '%' . $search . '%';

    $params['search_username'] = $term;
    $params['search_action'] = $term;
    $params['search_description'] = $term;
}

if ($module !== '') {
    $sql .= " AND module = :module";
    $params['module'] = $module;
}

$sql .= " ORDER BY created_at DESC LIMIT 250";

$statement = $pdo->prepare($sql);
$statement->execute($params);
$logs = $statement->fetchAll();

$modules = $pdo->query(
    "SELECT DISTINCT module
     FROM audit_logs
     WHERE module IS NOT NULL
       AND module <> ''
     ORDER BY module"
)->fetchAll(PDO::FETCH_COLUMN);

include '../includes/superadmin-header.php';
?>

<div class="dashboard">
    <?php include '../includes/superadmin-sidebar.php'; ?>

    <main class="content">
        <div class="page-header">
            <div>
                <h1 class="gold-title">Audit Logs</h1>
                <p>Review important user and system activities.</p>
            </div>
        </div>

        <section class="card audit-filter-card">
            <div class="audit-section-heading">
                <h2>Search &amp; Filter</h2>
            </div>

            <form class="audit-filter-form" method="GET">
                <div class="filter-field">
                    <label for="search">Search Logs</label>

                    <input
                        id="search"
                        name="search"
                        type="text"
                        value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Username, action, or description"
                    >
                </div>

                <div class="filter-field">
                    <label for="module">Module</label>

                    <select id="module" name="module">
                        <option value="">All modules</option>

                        <?php foreach ($modules as $moduleOption): ?>
                            <option
                                value="<?= htmlspecialchars($moduleOption, ENT_QUOTES, 'UTF-8') ?>"
                                <?= $module === $moduleOption ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($moduleOption, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-actions audit-filter-actions">
                    <button class="btn btn-gold" type="submit">
                        Filter
                    </button>

                    <a class="btn btn-light" href="audit-logs.php">
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <section class="card audit-table-card">
            <div class="audit-table-heading">
                <h2>System Activity</h2>
                <p><?= count($logs) ?> log record(s) displayed.</p>
            </div>

            <div class="table-wrap audit-table-wrap">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>Date &amp; Time</th>
                            <th>User</th>
                            <th>Module</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (!$logs): ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                No audit logs found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="audit-date">
                                    <?= date('M d, Y', strtotime($log['created_at'])) ?>
                                    <span>
                                        <?= date('h:i A', strtotime($log['created_at'])) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars($log['username'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <span class="audit-module">
                                        <?= htmlspecialchars($log['module'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td class="audit-description">
                                    <?= htmlspecialchars($log['description'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($log['ip_address'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>
