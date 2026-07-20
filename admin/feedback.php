<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/admin-helper.php';

$pageTitle = 'Feedback';

include '../includes/admin-header.php';
?>

<div class="admin-layout">
    <?php include '../includes/admin-sidebar.php'; ?>

    <main class="admin-content">
        <div class="admin-page-header">
            <div>
                <h1>Feedback</h1>
                <p>Review customer satisfaction, ratings, and comments.</p>
            </div>
        </div>

        
<?php $rows = adminFetchRows($pdo, 'feedback'); ?>

<section class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
<th>Customer</th>
<th>Rating</th>
<th>Comment</th>
<th>Status</th>
<th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr>
                    <td colspan="6" class="admin-empty">
                        No feedback records found.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) ($row['id'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars((string) ($row['customer_id'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars((string) ($row['rating'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars((string) ($row['comment'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars((string) ($row['status'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars((string) ($row['created_at'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
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
