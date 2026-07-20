<?php
declare(strict_types=1);

require_once '../includes/staff-auth.php';
require_once '../config/database.php';

$pageTitle = 'Change Password';
$staffId = (int) $_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $statement = $pdo->prepare(
        "SELECT password
         FROM users
         WHERE id = ?
           AND role = 'staff'
         LIMIT 1"
    );
    $statement->execute([$staffId]);
    $hash = $statement->fetchColumn();

    if (!$hash || !password_verify($currentPassword, $hash)) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'New password must contain at least 6 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New passwords do not match.';
    } else {
        $statement = $pdo->prepare(
            "UPDATE users
             SET password = ?
             WHERE id = ?
               AND role = 'staff'"
        );
        $statement->execute([
            password_hash($newPassword, PASSWORD_DEFAULT),
            $staffId,
        ]);

        $message = 'Password updated successfully.';
    }
}

include '../includes/staff-header.php';
?>

<div class="staff-layout">
    <?php include '../includes/staff-sidebar.php'; ?>

    <main class="staff-content">
        <div class="staff-page-header">
            <div>
                <h1>Change Password</h1>
                <p>Update the password used to access your staff account.</p>
            </div>

            <a class="staff-btn staff-btn-light" href="profile.php">
                Back to Profile
            </a>
        </div>

        <?php if ($message !== ''): ?>
            <div class="staff-alert staff-alert-success">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="staff-alert staff-alert-danger">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <section class="staff-card staff-password-card">
            <form method="POST">
                <div class="staff-field">
                    <label for="current_password">Current Password</label>
                    <input
                        id="current_password"
                        name="current_password"
                        type="password"
                        required
                    >
                </div>

                <div class="staff-field">
                    <label for="new_password">New Password</label>
                    <input
                        id="new_password"
                        name="new_password"
                        type="password"
                        required
                    >
                </div>

                <div class="staff-field">
                    <label for="confirm_password">Confirm New Password</label>
                    <input
                        id="confirm_password"
                        name="confirm_password"
                        type="password"
                        required
                    >
                </div>

                <div class="staff-actions">
                    <button class="staff-btn staff-btn-primary" type="submit">
                        Update Password
                    </button>
                </div>
            </form>
        </section>

        <?php include '../includes/staff-footer.php'; ?>
    </main>
</div>
