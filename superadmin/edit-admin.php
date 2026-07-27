<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
require_once '../includes/branch-context.php';

$pageTitle = 'Edit Administrator';
$error = '';

$adminId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$adminId) {
    header('Location: admins.php');
    exit;
}

$statement = $pdo->prepare(
    "SELECT id, fullname, username, email, status, branch_id
     FROM users
     WHERE id = ? AND role = 'admin'
     LIMIT 1"
);
$statement->execute([$adminId]);
$administrator = $statement->fetch();

if (!$administrator) {
    header('Location: admins.php');
    exit;
}

$fullname = $administrator['fullname'];
$username = $administrator['username'];
$email = $administrator['email'];
$status = $administrator['status'];
$branchId = (int)($administrator['branch_id'] ?? 0);
$branches = activeBranches($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $branchId = (int)($_POST['branch_id'] ?? 0);
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($fullname === '' || $username === '' || $email === '' || $branchId <= 0) {
        $error = 'Please complete all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!in_array($status, ['active', 'inactive'], true)) {
        $error = 'Invalid account status.';
    } elseif ($newPassword !== '' && strlen($newPassword) < 6) {
        $error = 'The new password must contain at least 6 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'The new passwords do not match.';
    } else {
        $check = $pdo->prepare(
            "SELECT id
             FROM users
             WHERE (username = :username OR email = :email)
               AND id != :id
             LIMIT 1"
        );

        $check->execute([
            'username' => $username,
            'email' => $email,
            'id' => $adminId,
        ]);

        if ($check->fetch()) {
            $error = 'The username or email address is already in use.';
        } else {
            if ($newPassword !== '') {
                $update = $pdo->prepare(
                    "UPDATE users
                     SET fullname = :fullname,
                         username = :username,
                         email = :email,
                         password = :password,
                         status = :status,
                         branch_id = :branch_id
                     WHERE id = :id AND role = 'admin'"
                );

                $update->execute([
                    'fullname' => $fullname,
                    'username' => $username,
                    'email' => $email,
                    'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                    'status' => $status,
                    'branch_id' => $branchId,
                    'id' => $adminId,
                ]);
            } else {
                $update = $pdo->prepare(
                    "UPDATE users
                     SET fullname = :fullname,
                         username = :username,
                         email = :email,
                         status = :status,
                         branch_id = :branch_id
                     WHERE id = :id AND role = 'admin'"
                );

                $update->execute([
                    'fullname' => $fullname,
                    'username' => $username,
                    'email' => $email,
                    'status' => $status,
                    'branch_id' => $branchId,
                    'id' => $adminId,
                ]);
            }

            header('Location: admins.php');
            exit;
        }
    }
}

include '../includes/superadmin-header.php';
?>

<div class="dashboard">
    <?php include '../includes/superadmin-sidebar.php'; ?>

    <main class="content">
        <div class="page-header">
            <div>
                <h1 class="gold-title">Edit Administrator</h1>
                <p>Update the selected administrator account.</p>
            </div>

            <a class="btn btn-light" href="admins.php">Back</a>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <section class="card form-card">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group form-span-2">
                        <label for="fullname">Full Name</label>
                        <input id="fullname" name="fullname" type="text"
                               value="<?= htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input id="username" name="username" type="text"
                               value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input id="email" name="email" type="email"
                               value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input id="new_password" name="new_password" type="password"
                               placeholder="Leave blank to keep current password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input id="confirm_password" name="confirm_password" type="password">
                    </div>

                    <div class="form-group">
                        <label for="branch_id">Assigned Branch</label>
                        <select id="branch_id" name="branch_id" required>
                            <option value="">Select branch</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?= (int)$branch['id'] ?>" <?= $branchId === (int)$branch['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($branch['branch_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Account Status</label>
                        <select id="status" name="status">
                            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-gold" type="submit">Save Changes</button>
                    <a class="btn btn-light" href="admins.php">Cancel</a>
                </div>
            </form>
        </section>

        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>
