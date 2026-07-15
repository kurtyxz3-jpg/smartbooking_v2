<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';

$pageTitle = 'Administrator Management';
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $adminId = filter_input(INPUT_POST, 'admin_id', FILTER_VALIDATE_INT);

    if ($adminId && $action === 'toggle_status') {
        $statement = $pdo->prepare(
            "UPDATE users
             SET status = CASE
                 WHEN status = 'active' THEN 'inactive'
                 ELSE 'active'
             END
             WHERE id = ? AND role = 'admin'"
        );
        $statement->execute([$adminId]);
        $message = 'Administrator status updated successfully.';
    }

    if ($adminId && $action === 'delete') {
        $statement = $pdo->prepare(
            "DELETE FROM users
             WHERE id = ? AND role = 'admin'"
        );
        $statement->execute([$adminId]);
        $message = 'Administrator account deleted successfully.';
        $messageType = 'danger';
    }
}

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';

$createdAtCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'created_at'");
$hasCreatedAt = (bool) $createdAtCheck->fetch();

$createdAtSelect = $hasCreatedAt
    ? 'created_at'
    : 'NULL AS created_at';

$sql = "SELECT id, fullname, username, email, status, {$createdAtSelect}
        FROM users
        WHERE role = 'admin'";

$params = [];

if ($search !== '') {
    $sql .= " AND (
        fullname LIKE :search_name
        OR username LIKE :search_username
        OR email LIKE :search_email
    )";

    $searchTerm = '%' . $search . '%';
    $params['search_name'] = $searchTerm;
    $params['search_username'] = $searchTerm;
    $params['search_email'] = $searchTerm;
}

if (in_array($status, ['active', 'inactive'], true)) {
    $sql .= " AND status = :status";
    $params['status'] = $status;
}

$sql .= $hasCreatedAt
    ? ' ORDER BY created_at DESC'
    : ' ORDER BY id DESC';

$statement = $pdo->prepare($sql);
$statement->execute($params);
$administrators = $statement->fetchAll();

include '../includes/superadmin-header.php';
?>

<div class="dashboard">
    <?php include '../includes/superadmin-sidebar.php'; ?>

    <main class="content">
        <div class="page-header">
            <div>
                <h1 class="gold-title">Administrators</h1>
                <p>Manage administrator accounts and access status.</p>
            </div>

            <a class="btn btn-gold" href="add-admin.php">
                Add Administrator
            </a>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <form class="filter-bar" method="GET">
                <div class="filter-field">
                    <label for="search">Search</label>
                    <input
                        id="search"
                        name="search"
                        type="text"
                        value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Name, username, or email"
                    >
                </div>

                <div class="filter-field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All statuses</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button class="btn btn-gold" type="submit">Filter</button>
                    <a class="btn btn-light" href="admins.php">Reset</a>
                </div>
            </form>
        </section>

        <section class="card table-card">
            <div class="table-heading">
                <h3>Administrator Accounts</h3>
                <p><?= count($administrators) ?> record(s) found.</p>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Administrator</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Date Created</th>
                            <th class="actions-column">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (!$administrators): ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                No administrator accounts found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($administrators as $administrator): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($administrator['fullname'], ENT_QUOTES, 'UTF-8') ?></strong>
                                </td>

                                <td><?= htmlspecialchars($administrator['username'], ENT_QUOTES, 'UTF-8') ?></td>

                                <td><?= htmlspecialchars($administrator['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>

                                <td>
                                    <span class="badge badge-<?= $administrator['status'] === 'active' ? 'success' : 'inactive' ?>">
                                        <?= ucfirst($administrator['status']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= !empty($administrator['created_at'])
                                        ? date('M d, Y', strtotime($administrator['created_at']))
                                        : '—' ?>
                                </td>

                                <td>
                                    <div class="table-actions">
                                        <a class="action-link"
                                           href="edit-admin.php?id=<?= (int) $administrator['id'] ?>">
                                            Edit
                                        </a>

                                        <form method="POST">
                                            <input type="hidden" name="admin_id" value="<?= (int) $administrator['id'] ?>">
                                            <input type="hidden" name="action" value="toggle_status">

                                            <button class="action-link action-button" type="submit">
                                                <?= $administrator['status'] === 'active'
                                                    ? 'Deactivate'
                                                    : 'Activate' ?>
                                            </button>
                                        </form>

                                        <form method="POST"
                                              onsubmit="return confirm('Delete this administrator account?');">
                                            <input type="hidden" name="admin_id" value="<?= (int) $administrator['id'] ?>">
                                            <input type="hidden" name="action" value="delete">

                                            <button class="action-link action-button action-danger" type="submit">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
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
