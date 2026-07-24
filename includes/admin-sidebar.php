<?php
$currentPage = basename($_SERVER['PHP_SELF']);

require_once __DIR__ . '/account-display-helper.php';
require_once __DIR__ . '/brand-assets.php';
$sidebarAccount = currentAccountDisplay($pdo ?? null);
$sidebarPhoto = currentAccountPhotoUrl($sidebarAccount);?>

<aside class="admin-sidebar">
    <div class="admin-sidebar-brand">
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
                <small>Administrator</small>
            </span>
        </a>
    </div>

    <nav class="admin-sidebar-nav">
        <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
            Dashboard
        </a>

        <a href="bookings.php" class="<?= $currentPage === 'bookings.php' ? 'active' : '' ?>">
            Bookings
        </a>

        <a href="services.php" class="<?= $currentPage === 'services.php' ? 'active' : '' ?>">
            Services
        </a>

        <a href="inventory.php" class="<?= $currentPage === 'inventory.php' ? 'active' : '' ?>">
            Inventory
        </a>

        <a href="staff.php" class="<?= $currentPage === 'staff.php' ? 'active' : '' ?>">
            Staff
        </a>

        <a href="promotions.php" class="<?= $currentPage === 'promotions.php' ? 'active' : '' ?>">
            Promotions
        </a>

        <a href="payments.php" class="<?= $currentPage === 'payments.php' ? 'active' : '' ?>">
            Payments
        </a>

        <a href="feedback.php" class="<?= $currentPage === 'feedback.php' ? 'active' : '' ?>">
            Feedback
        </a>

        <a href="reports.php" class="<?= $currentPage === 'reports.php' ? 'active' : '' ?>">
            Reports
        </a>

        <div class="admin-sidebar-divider"></div>

        <a href="profile.php" class="<?= $currentPage === 'profile.php' ? 'active' : '' ?>">
            Profile
        </a>

        <a href="../auth/logout.php">
            Logout
        </a>
    </nav>
</aside>
