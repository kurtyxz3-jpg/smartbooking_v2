<?php
require_once __DIR__ . '/language.php';
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">
    <div class="sidebar-brand">
        <h2>NBW</h2>
        <p><?= htmlspecialchars(t('super_administrator'), ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
            <?= htmlspecialchars(t('dashboard'), ENT_QUOTES, 'UTF-8') ?>
        </a>

        <a href="admins.php" class="<?= in_array($currentPage, ['admins.php', 'add-admin.php', 'edit-admin.php'], true) ? 'active' : '' ?>">
            <?= htmlspecialchars(t('administrators'), ENT_QUOTES, 'UTF-8') ?>
        </a>

        <a href="staff.php" class="<?= in_array($currentPage, ['staff.php', 'add-staff.php', 'edit-staff.php', 'staff-roles.php'], true) ? 'active' : '' ?>">
            <?= htmlspecialchars(t('staff_accounts'), ENT_QUOTES, 'UTF-8') ?>
        </a>

        <a href="employment-records.php" class="<?= in_array($currentPage, ['employment-records.php', 'add-employment-record.php', 'edit-employment-record.php'], true) ? 'active' : '' ?>">
            <?= htmlspecialchars(t('employment_records'), ENT_QUOTES, 'UTF-8') ?>
        </a>

        <a href="sales-analytics.php" class="<?= $currentPage === 'sales-analytics.php' ? 'active' : '' ?>">
            <?= htmlspecialchars(t('sales_analytics'), ENT_QUOTES, 'UTF-8') ?>
        </a>

        <a href="reports.php" class="<?= $currentPage === 'reports.php' ? 'active' : '' ?>">
            <?= htmlspecialchars(t('reports'), ENT_QUOTES, 'UTF-8') ?>
        </a>

        <a href="system-settings.php" class="<?= $currentPage === 'system-settings.php' ? 'active' : '' ?>">
            <?= htmlspecialchars(t('system_settings'), ENT_QUOTES, 'UTF-8') ?>
        </a>

        <a href="ai-settings.php" class="<?= $currentPage === 'ai-settings.php' ? 'active' : '' ?>">
            <?= htmlspecialchars(t('ai_settings'), ENT_QUOTES, 'UTF-8') ?>
        </a>

        <a href="notification-settings.php" class="<?= $currentPage === 'notification-settings.php' ? 'active' : '' ?>">
            <?= htmlspecialchars(t('notifications'), ENT_QUOTES, 'UTF-8') ?>
        </a>

        <a href="audit-logs.php" class="<?= $currentPage === 'audit-logs.php' ? 'active' : '' ?>">
            <?= htmlspecialchars(t('audit_logs'), ENT_QUOTES, 'UTF-8') ?>
        </a>

        <a href="backup.php" class="<?= $currentPage === 'backup.php' ? 'active' : '' ?>">
            <?= htmlspecialchars(t('backup_restore'), ENT_QUOTES, 'UTF-8') ?>
        </a>

        <a href="language-settings.php" class="<?= $currentPage === 'language-settings.php' ? 'active' : '' ?>">
            <?= htmlspecialchars(t('language_settings'), ENT_QUOTES, 'UTF-8') ?>
        </a>

        <div class="sidebar-divider"></div>

        <a href="profile.php" class="<?= in_array($currentPage, ['profile.php', 'edit-profile.php', 'change-password.php'], true) ? 'active' : '' ?>">
            <?= htmlspecialchars(t('profile'), ENT_QUOTES, 'UTF-8') ?>
        </a>

        <a href="../auth/logout.php">
            <?= htmlspecialchars(t('logout'), ENT_QUOTES, 'UTF-8') ?>
        </a>
    </nav>
</aside>
