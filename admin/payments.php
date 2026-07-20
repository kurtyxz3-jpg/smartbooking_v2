<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/admin-helper.php';

$pageTitle = 'Payments';

include '../includes/admin-header.php';
?>

<div class="admin-layout">
    <?php include '../includes/admin-sidebar.php'; ?>

    <main class="admin-content">
        <div class="admin-page-header">
            <div>
                <h1>Payments</h1>
                <p>Review customer payments and proof-of-payment status.</p>
            </div>
        </div>

        
<?php $rows = adminFetchRows($pdo, 'payments'); ?>

<section class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
<th>Booking</th>
<th>Amount</th>
<th>Method</th>
<th>Reference</th>
<th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr>
                    <td colspan="6" class="admin-empty">
                        No payment records found.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) ($row['id'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars((string) ($row['booking_id'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars((string) ($row['amount'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars((string) ($row['payment_method'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars((string) ($row['reference_number'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
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
