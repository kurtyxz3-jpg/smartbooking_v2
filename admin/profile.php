<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/admin-helper.php';

$pageTitle = 'Profile';

include '../includes/admin-header.php';
?>

<div class="admin-layout">
    <?php include '../includes/admin-sidebar.php'; ?>

    <main class="admin-content">
        <div class="admin-page-header">
            <div>
                <h1>Profile</h1>
                <p>View your administrator account information.</p>
            </div>
        </div>

        
<section class="admin-card" style="max-width:760px;">
    <h2>Administrator Profile</h2>

    <div class="admin-grid">
        <div class="admin-field">
            <label>Full Name</label>
            <input type="text" value="<?= htmlspecialchars($_SESSION['fullname'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly>
        </div>

        <div class="admin-field">
            <label>Username</label>
            <input type="text" value="<?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly>
        </div>

        <div class="admin-field">
            <label>Email</label>
            <input type="email" value="<?= htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly>
        </div>
    </div>
</section>


        <?php include '../includes/admin-footer.php'; ?>
    </main>
</div>
