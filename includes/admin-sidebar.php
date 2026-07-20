<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="admin-sidebar">
    <div class="admin-sidebar-brand">
        <h2>NBW</h2>
        <p>Administrator</p>
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
