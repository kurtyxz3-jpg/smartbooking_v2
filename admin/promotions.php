<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/admin-helper.php';

$pageTitle = 'Promotions';

include '../includes/admin-header.php';
?>

<div class="admin-layout">
    <?php include '../includes/admin-sidebar.php'; ?>

    <main class="admin-content">
        <div class="admin-page-header">
            <div>
                <h1>Promotions</h1>
                <p>Manage promotional campaigns and seasonal offers.</p>
            </div>
        </div>

        
<?php $rows = adminFetchRows($pdo, 'promotions'); ?>

<section class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
<th>Promotion</th>
<th>Discount</th>
<th>Start Date</th>
<th>End Date</th>
<th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr>
                    <td colspan="6" class="admin-empty">
                        No promotion records found.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) ($row['id'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars((string) ($row['title'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars((string) ($row['discount_value'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars((string) ($row['start_date'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars((string) ($row['end_date'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
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
