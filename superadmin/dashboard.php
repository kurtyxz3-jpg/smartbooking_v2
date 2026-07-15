<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';

$pageTitle = 'Super Admin Dashboard';

$adminCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM users WHERE role = 'admin'"
)->fetchColumn();

$staffCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM users WHERE role = 'staff'"
)->fetchColumn();

$customerCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM users WHERE role = 'customer'"
)->fetchColumn();

$appointmentCount = 0;

include '../includes/superadmin-header.php';
?>

<div class="dashboard">
    <?php include '../includes/superadmin-sidebar.php'; ?>

    <main class="content">
        <div class="content-heading">
            <h1 class="gold-title">Super Admin Dashboard</h1>
            <p>
                Welcome,
                <?= htmlspecialchars(
                    $_SESSION['fullname'] ?? 'Super Administrator',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>.
            </p>
        </div>

        <section class="kpis">
            <article class="kpi">
                <small>Administrators</small>
                <h2><?= $adminCount ?></h2>
            </article>

            <article class="kpi">
                <small>Staff Members</small>
                <h2><?= $staffCount ?></h2>
            </article>

            <article class="kpi">
                <small>Customers</small>
                <h2><?= $customerCount ?></h2>
            </article>

            <article class="kpi">
                <small>Appointments</small>
                <h2><?= $appointmentCount ?></h2>
            </article>
        </section>

        <section class="dashboard-grid">
            <article class="card">
                <h3>Recent System Activity</h3>

                <ul class="activity-list">
                    <li>
                        <strong>Super Administrator signed in</strong>
                        <p>Your current session is active.</p>
                    </li>

                    <li>
                        <strong>SmartBooking Version 2 initialized</strong>
                        <p>The Super Admin module is ready for development.</p>
                    </li>
                </ul>
            </article>

            <article class="card">
                <h3>Quick Actions</h3>

                <div class="quick-actions">
                    <a class="quick-action" href="add-admin.php">
                        Add Administrator
                    </a>

                    <a class="quick-action" href="system-settings.php">
                        System Settings
                    </a>

                    <a class="quick-action" href="backup.php">
                        Create Backup
                    </a>

                    <a class="quick-action" href="audit-logs.php">
                        View Audit Logs
                    </a>
                </div>
            </article>
        </section>

        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>
