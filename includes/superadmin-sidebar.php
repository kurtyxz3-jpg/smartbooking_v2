<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">
    <div class="sidebar-brand">
        <h2>NBW</h2>
        <p>Super Administrator</p>
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard.php"
           class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
            Dashboard
        </a>

        <a href="admins.php"
           class="<?= in_array($currentPage, ['admins.php', 'add-admin.php', 'edit-admin.php'], true) ? 'active' : '' ?>">
            Administrators
        </a>

        <a href="staff.php"
           class="<?= in_array($currentPage, ['staff.php', 'add-staff.php', 'edit-staff.php', 'staff-roles.php'], true) ? 'active' : '' ?>">
            Staff Accounts
        </a>

        <a href="employment-records.php"
           class="<?= in_array($currentPage, ['employment-records.php', 'add-employment-record.php', 'edit-employment-record.php'], true) ? 'active' : '' ?>">
            Employment Records
        </a>

        <a href="sales-analytics.php"
           class="<?= $currentPage === 'sales-analytics.php' ? 'active' : '' ?>">
            Sales Analytics
        </a>

        <a href="reports.php"
           class="<?= $currentPage === 'reports.php' ? 'active' : '' ?>">
            Reports
        </a>

        <a href="system-settings.php"
           class="<?= $currentPage === 'system-settings.php' ? 'active' : '' ?>">
            System Settings
        </a>

        <a href="ai-settings.php"
           class="<?= $currentPage === 'ai-settings.php' ? 'active' : '' ?>">
            AI Settings
        </a>

        <a href="notification-settings.php"
           class="<?= $currentPage === 'notification-settings.php' ? 'active' : '' ?>">
            Notifications
        </a>

        <a href="audit-logs.php"
           class="<?= $currentPage === 'audit-logs.php' ? 'active' : '' ?>">
            Audit Logs
        </a>

        <a href="backup.php"
           class="<?= $currentPage === 'backup.php' ? 'active' : '' ?>">
            Backup &amp; Restore
        </a>

        <div class="sidebar-divider"></div>

        <a href="profile.php"
           class="<?= in_array($currentPage, ['profile.php', 'edit-profile.php', 'change-password.php'], true) ? 'active' : '' ?>">
            Profile
        </a>

        <a href="../auth/logout.php">
            Logout
        </a>
    </nav>
</aside>
