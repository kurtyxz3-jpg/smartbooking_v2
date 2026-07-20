<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="staff-sidebar">
    <div class="staff-sidebar-brand">
        <h2>NBW</h2>
        <p>Staff Portal</p>
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
