<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/language.php';

require_once '../includes/password-recovery.php';
header('Cache-Control: no-store');
$message = '';
recoveryCsrf();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!recoveryVerifyCsrf()) {
        $message = 'Your session expired. Refresh the page and try again.';
    } else {
        $message = 'If an active account matches that email, a password-reset link will be emailed shortly. Check your inbox and spam folder.';
        try { recoveryRequest($pdo, (string)($_POST['email'] ?? '')); }
        catch (Throwable $exception) { error_log('SmartBooking password recovery could not be queued. Check migration and database configuration.'); }
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
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(recoveryCsrf(), ENT_QUOTES, 'UTF-8') ?>">
                <div class="auth-field">
                    <label for="email">Email Address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required>
                </div>
                <button class="auth-submit">Email reset link ↗</button>
            </form>
            <p class="auth-switch"><a href="login.php">Return to sign in</a></p>
        </div>
    </section>
</main>
</body>
</html>
