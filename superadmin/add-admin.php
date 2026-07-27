<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
require_once '../includes/branch-context.php';

$pageTitle = 'Add Administrator';
$error = '';

$fullname = '';
$username = '';
$email = '';
$status = 'active';
$branchId = 0;
$branches = activeBranches($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $status = $_POST['status'] ?? 'active';
    $branchId = (int) ($_POST['branch_id'] ?? 0);

    if ($fullname === '' || $username === '' || $email === '' || $password === '' || $branchId <= 0) {
        $error = 'Please complete all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must contain at least 6 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($status, ['active', 'inactive'], true)) {
        $error = 'Invalid account status.';
    } else {
        $check = $pdo->prepare(
            "SELECT id
             FROM users
             WHERE username = :username OR email = :email
             LIMIT 1"
        );

        $check->execute([
            'username' => $username,
            'email' => $email,
        ]);

        if ($check->fetch()) {
            $error = 'The username or email address is already in use.';
        } else {
            $statement = $pdo->prepare(
                "INSERT INTO users
                    (fullname, username, email, password, role, status, branch_id)
                 VALUES
                    (:fullname, :username, :email, :password, 'admin', :status, :branch_id)"
            );

            $statement->execute([
                'fullname' => $fullname,
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'status' => $status,
                'branch_id' => $branchId,
            ]);

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
                <h1 class="gold-title">Add Administrator</h1>
                <p>Create a new administrator account.</p>
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
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input id="confirm_password" name="confirm_password" type="password" required>
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
                    <button class="btn btn-gold" type="submit">Create Administrator</button>
                    <a class="btn btn-light" href="admins.php">Cancel</a>
                </div>
            </form>
        </section>

        <?php include '../includes/superadmin-footer.php'; ?>
    </main>
</div>
