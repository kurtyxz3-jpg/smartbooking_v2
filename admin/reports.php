<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/admin-helper.php';

$pageTitle = 'Reports';

include '../includes/admin-header.php';
?>

<div class="admin-layout">
    <?php include '../includes/admin-sidebar.php'; ?>

    <main class="admin-content">
        <div class="admin-page-header">
            <div>
                <h1>Reports</h1>
                <p>View operational summaries and prepare future sales reporting.</p>
            </div>
        </div>

        
<section class="admin-kpis">
    <article class="admin-kpi"><small>Bookings</small><h2><?= adminSafeCount($pdo, 'bookings') ?></h2></article>
    <article class="admin-kpi"><small>Payments</small><h2><?= adminSafeCount($pdo, 'payments') ?></h2></article>
    <article class="admin-kpi"><small>Feedback</small><h2><?= adminSafeCount($pdo, 'feedback') ?></h2></article>
    <article class="admin-kpi"><small>Promotions</small><h2><?= adminSafeCount($pdo, 'promotions') ?></h2></article>
</section>

<section class="admin-card" style="margin-top:16px;">
    <h2>Reports Overview</h2>
    <p>The detailed sales report module can be added here later without changing the Admin navigation structure.</p>
</section>


        <?php include '../includes/admin-footer.php'; ?>
    </main>
</div>
