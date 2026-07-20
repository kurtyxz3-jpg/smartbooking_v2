<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/admin-helper.php';

$pageTitle = 'Staff';

include '../includes/admin-header.php';
?>

<div class="admin-layout">
    <?php include '../includes/admin-sidebar.php'; ?>

    <main class="admin-content">
        <div class="admin-page-header">
            <div>
                <h1>Staff</h1>
                <p>View staff accounts and salon team information.</p>
            </div>
        </div>

        
<?php $rows = adminFetchRows($pdo, 'users'); ?>

<section class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
<th>Full Name</th>
<th>Username</th>
<th>Email</th>
<th>Role</th>
<th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr>
                    <td colspan="6" class="admin-empty">
                        No staff records found.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) ($row['id'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars((string) ($row['fullname'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars((string) ($row['username'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars((string) ($row['email'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars((string) ($row['role'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars((string) ($row['status'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>


        <?php include '../includes/admin-footer.php'; ?>
    </main>
</div>
