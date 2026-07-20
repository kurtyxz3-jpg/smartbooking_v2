<?php
declare(strict_types=1);

require_once '../includes/staff-auth.php';
require_once '../config/database.php';

$pageTitle = 'Profile';
$staffId = (int) $_SESSION['user_id'];

$statement = $pdo->prepare(
    "SELECT
        u.fullname,
        u.username,
        u.email,
        u.phone_number,
        u.status,
        sp.specialty,
        sp.branch,
        sp.employment_status
     FROM users u
     LEFT JOIN staff_profiles sp ON sp.user_id = u.id
     WHERE u.id = ?
       AND u.role = 'staff'
     LIMIT 1"
);
$statement->execute([$staffId]);
$profile = $statement->fetch() ?: [];

include '../includes/staff-header.php';
?>

<div class="staff-layout">
    <?php include '../includes/staff-sidebar.php'; ?>

    <main class="staff-content">
        <div class="staff-page-header">
            <div>
                <h1>My Profile</h1>
                <p>Review your account and employment information.</p>
            </div>

            <a class="staff-btn staff-btn-light" href="change-password.php">
                Change Password
            </a>
        </div>

        <section class="staff-profile-grid">
            <article class="staff-card staff-profile-card">
                <div class="staff-profile-avatar">
                    <?= strtoupper(substr((string) ($profile['fullname'] ?? 'S'), 0, 1)) ?>
                </div>

                <h2>
                    <?= htmlspecialchars($profile['fullname'] ?? 'Staff Member', ENT_QUOTES, 'UTF-8') ?>
                </h2>

                <p>
                    @<?= htmlspecialchars($profile['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </p>

                <span class="staff-badge <?= staffStatusClass((string) ($profile['status'] ?? 'active')) ?>">
                    <?= ucfirst((string) ($profile['status'] ?? 'active')) ?>
                </span>
            </article>

            <article class="staff-card">
                <h2>Account Information</h2>

                <div class="staff-profile-details">
                    <div>
                        <span>Email Address</span>
                        <strong><?= htmlspecialchars($profile['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>

                    <div>
                        <span>Phone Number</span>
                        <strong><?= htmlspecialchars($profile['phone_number'] ?? '—', ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>

                    <div>
                        <span>Specialty</span>
                        <strong><?= htmlspecialchars($profile['specialty'] ?? 'Unassigned', ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>

                    <div>
                        <span>Branch</span>
                        <strong><?= htmlspecialchars($profile['branch'] ?? 'Main Branch', ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>

                    <div>
                        <span>Employment Status</span>
                        <strong>
                            <?= htmlspecialchars(
                                ucfirst(str_replace('_', ' ', $profile['employment_status'] ?? 'full_time')),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </strong>
                    </div>
                </div>
            </article>
        </section>

        <?php include '../includes/staff-footer.php'; ?>
    </main>
</div>
