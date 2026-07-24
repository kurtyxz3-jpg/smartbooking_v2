<?php
$currentPage = basename($_SERVER['PHP_SELF']);

require_once __DIR__ . '/account-display-helper.php';
require_once __DIR__ . '/brand-assets.php';
$sidebarAccount = currentAccountDisplay($pdo ?? null);
$sidebarPhoto = currentAccountPhotoUrl($sidebarAccount);?>

<aside class="staff-sidebar">
    <div class="staff-sidebar-brand">
        <a class="role-sidebar-logo-link" href="dashboard.php">
            <img
                class="nbw-official-logo role-sidebar-business-logo"
                src="<?= htmlspecialchars(
                    smartBookingBrandLogo(),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                onerror="this.onerror=null;this.src='<?= htmlspecialchars(
                    smartBookingBrandLogoFallback(),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>';"
                alt="Nails and Body Worx"
            >
        </a>

        <a class="role-sidebar-account" href="profile.php">
            <span class="role-sidebar-avatar">
                <?php if ($sidebarPhoto): ?>
                    <img
                        src="<?= htmlspecialchars($sidebarPhoto, ENT_QUOTES, 'UTF-8') ?>"
                        alt="<?= htmlspecialchars($sidebarAccount['fullname'], ENT_QUOTES, 'UTF-8') ?>"
                    >
                <?php else: ?>
                    <b><?= htmlspecialchars(
                        currentAccountInitials($sidebarAccount['fullname']),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?></b>
                <?php endif; ?>
            </span>

            <span class="role-sidebar-account-copy">
                <strong><?= htmlspecialchars(
                    $sidebarAccount['fullname'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?></strong>
                <small>Staff Portal</small>
            </span>
        </a>
    </div>

    <nav class="staff-sidebar-nav">
        <a href="dashboard.php"
           class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
            Dashboard
        </a>

        <a href="bookings.php"
           class="<?= $currentPage === 'bookings.php' ? 'active' : '' ?>">
            My Bookings
        </a>

        <a href="schedule.php"
           class="<?= $currentPage === 'schedule.php' ? 'active' : '' ?>">
            My Schedule
        </a>

        <a href="performance.php"
           class="<?= $currentPage === 'performance.php' ? 'active' : '' ?>">
            Performance
        </a>

        <div class="staff-sidebar-divider"></div>

        <a href="profile.php"
           class="<?= in_array($currentPage, ['profile.php', 'change-password.php'], true) ? 'active' : '' ?>">
            Profile
        </a>

        <a href="../auth/logout.php">
            Logout
        </a>
    </nav>
</aside>
