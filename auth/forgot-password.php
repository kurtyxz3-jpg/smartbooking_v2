<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/language.php';

$message = '';
$resetUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $message = 'If that email belongs to an account, a password-reset link has been prepared.';

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $statement = $pdo->prepare(
            "SELECT id FROM users WHERE email = ? AND status = 'active' LIMIT 1"
        );
        $statement->execute([$email]);
        $userId = (int) $statement->fetchColumn();

        if ($userId > 0) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $pdo->prepare(
                'DELETE FROM password_reset_tokens WHERE user_id = ?'
            )->execute([$userId]);
            $pdo->prepare(
                'INSERT INTO password_reset_tokens
                    (user_id, token_hash, expires_at)
                 VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))'
            )->execute([$userId, $tokenHash]);
            $resetUrl = 'http://localhost/smartbooking_v2/auth/reset-password.php?token='
                . urlencode($token);
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
    <title>Forgot Password | NBW</title>
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
            <p class="auth-kicker">Account recovery</p>
            <h1>Let’s get you back into your beauty space.</h1>
            <p>Enter the email connected to your SmartBooking account. Reset tokens expire after one hour.</p>
        </div>
    </section>
    <section class="auth-form-side">
        <div class="auth-card">
            <p class="auth-kicker">Password help</p>
            <h2>Reset access.</h2>
            <?php if ($message !== ''): ?>
                <div class="auth-alert success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="auth-field">
                    <label for="email">Email Address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required>
                </div>
                <button class="auth-submit">Prepare reset link ↗</button>
            </form>
            <?php if ($resetUrl !== ''): ?>
                <div class="dev-reset">
                    <strong>Local-development reset link:</strong><br>
                    <a href="<?= htmlspecialchars($resetUrl) ?>"><?= htmlspecialchars($resetUrl) ?></a><br>
                    <small>Replace this display with SMTP email delivery before public deployment.</small>
                </div>
            <?php endif; ?>
            <p class="auth-switch"><a href="login.php">Return to sign in</a></p>
        </div>
    </section>
</main>
</body>
</html>
