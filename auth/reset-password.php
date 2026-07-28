<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/language.php';

$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$error = '';
$success = '';
$resetRecord = null;

if ($token !== '') {
    $statement = $pdo->prepare(
        'SELECT id, user_id
         FROM password_reset_tokens
         WHERE token_hash = ?
           AND used_at IS NULL
           AND expires_at > NOW()
         LIMIT 1'
    );
    $statement->execute([hash('sha256', $token)]);
    $resetRecord = $statement->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$resetRecord) {
        $error = 'This reset link is invalid or expired.';
    } else {
        $password = (string) ($_POST['password'] ?? '');
        $confirmation = (string) ($_POST['confirm_password'] ?? '');

        if (
            strlen($password) < 8
            || !preg_match('/[A-Z]/', $password)
            || !preg_match('/[a-z]/', $password)
            || !preg_match('/[0-9]/', $password)
        ) {
            $error = 'Use at least 8 characters with uppercase, lowercase, and a number.';
        } elseif ($password !== $confirmation) {
            $error = 'Passwords do not match.';
        } else {
            $pdo->beginTransaction();
            try {
                $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([
                    password_hash($password, PASSWORD_DEFAULT),
                    (int) $resetRecord['user_id'],
                ]);
                $pdo->prepare(
                    'UPDATE password_reset_tokens SET used_at = NOW() WHERE id = ?'
                )->execute([(int) $resetRecord['id']]);
                $pdo->commit();
                $success = 'Password changed successfully. You may now sign in.';
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = 'The password could not be updated.';
            }
        }
    }
}
?>
<!doctype html>
<html
    lang="<?= htmlspecialchars($languageCode, ENT_QUOTES, 'UTF-8') ?>"
    data-theme="<?= htmlspecialchars($themeMode, ENT_QUOTES, 'UTF-8') ?>"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password | NBW</title>
    <script src="../assets/js/theme-bootstrap.js"></script>
    <link rel="stylesheet" href="../assets/css/customer-auth-v3.css">
    <link rel="stylesheet" href="../assets/css/accessibility-preferences.css">
    <link rel="stylesheet" href="../assets/css/global-theme-system.css?v=083">
</head>
<body class="auth-v3">
<main class="auth-layout">
    <section class="auth-showcase">
        <a class="auth-brand" href="../index.php">
            <span>N</span>
            <div>
                <strong>Nails &amp; Body Worx</strong>
                <small>SmartBooking</small>
            </div>
        </a>
        <div class="auth-copy">
            <p class="auth-kicker">Secure recovery</p>
            <h1>Create a fresh, secure password.</h1>
            <p>Your new password must contain at least eight characters, uppercase and lowercase letters, and a number.</p>
        </div>
    </section>
    <section class="auth-form-side">
        <div class="auth-card">
            <p class="auth-kicker">New password</p>
            <h2>Update password.</h2>
            <?php if ($error !== ''): ?>
                <div class="auth-alert error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="auth-alert success"><?= htmlspecialchars($success) ?></div>
                <p class="auth-switch"><a href="login.php">Continue to sign in</a></p>
            <?php elseif (!$resetRecord): ?>
                <div class="auth-alert error">This reset link is invalid or expired.</div>
                <p class="auth-switch"><a href="forgot-password.php">Request another link</a></p>
            <?php else: ?>
                <form method="post">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <div class="auth-field">
                        <label for="password">New Password</label>
                        <div class="auth-password">
                            <input id="password" name="password" type="password" required>
                            <button type="button" data-password="password">Show</button>
                        </div>
                    </div>
                    <div class="auth-field">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="auth-password">
                            <input id="confirm_password" name="confirm_password" type="password" required>
                            <button type="button" data-password="confirm_password">Show</button>
                        </div>
                    </div>
                    <button class="auth-submit">Save new password ↗</button>
                </form>
            <?php endif; ?>
        </div>
    </section>
</main>
<script src="../assets/js/customer-auth-v3.js"></script>
</body>
</html>
