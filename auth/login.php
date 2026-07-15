<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$roleRedirects = [
    'super_admin' => '../superadmin/dashboard.php',
    'admin' => '../admin/dashboard.php',
    'staff' => '../staff/dashboard.php',
    'customer' => '../customer/dashboard.php',
];

if (
    isset($_SESSION['user_id'], $_SESSION['role']) &&
    isset($roleRedirects[$_SESSION['role']])
) {
    header('Location: ' . $roleRedirects[$_SESSION['role']]);
    exit;
}

$error = '';
$loginValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginValue = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($loginValue === '' || $password === '') {
        $error = 'Please enter your username or email and password.';
    } else {
        /*
         * Use two different parameter names.
         * MySQL PDO with native prepared statements cannot reuse
         * the same named placeholder twice in one query.
         */
        $statement = $pdo->prepare(
            "SELECT id, fullname, username, email, password, role, status
             FROM users
             WHERE username = :username_login
                OR email = :email_login
             LIMIT 1"
        );

        $statement->execute([
            'username_login' => $loginValue,
            'email_login' => $loginValue,
        ]);

        $user = $statement->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $error = 'Invalid username/email or password.';
        } elseif ($user['status'] !== 'active') {
            $error = 'Your account is inactive. Please contact the system administrator.';
        } elseif (!isset($roleRedirects[$user['role']])) {
            $error = 'This account role is not supported.';
        } else {
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            header('Location: ' . $roleRedirects[$user['role']]);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | NBW</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p>Sign in to continue to Nails &amp; Body Worx.</p>

        <?php if ($error !== ''): ?>
            <div style="margin-bottom:16px;padding:12px 14px;border-radius:12px;background:#FBE7E6;color:#A94442;font-weight:700;">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="login">Username or Email</label>
                <input
                    id="login"
                    name="login"
                    type="text"
                    value="<?= htmlspecialchars($loginValue, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Enter username or email"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Enter password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit" class="btn btn-gold" style="width:100%;">
                Login
            </button>
        </form>

        <p style="margin-top:20px;text-align:center;">
            <a href="../index.php" style="color:#A97922;font-weight:700;">Back to Home</a>
        </p>
    </div>
</div>
</body>
</html>
