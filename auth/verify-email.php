<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/language.php';

$token = trim((string) ($_GET['token'] ?? ''));
$message = 'Invalid or expired verification link.';
$verified = false;

if ($token !== '') {
    $statement = $pdo->prepare(
        'SELECT user_id
         FROM email_verification_tokens
         WHERE token_hash = ?
           AND used_at IS NULL
           AND expires_at > NOW()
         LIMIT 1'
    );
    $statement->execute([hash('sha256', $token)]);
    $userId = (int) $statement->fetchColumn();

    if ($userId > 0) {
        $pdo->beginTransaction();
        try {
            $pdo->prepare(
                'UPDATE customer_profiles SET email_verified_at = NOW() WHERE user_id = ?'
            )->execute([$userId]);
            $pdo->prepare(
                'UPDATE email_verification_tokens
                 SET used_at = NOW()
                 WHERE user_id = ? AND used_at IS NULL'
            )->execute([$userId]);
            $pdo->commit();
            $verified = true;
            $message = 'Your email address has been verified.';
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
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
    <title>Email Verification | NBW</title>
    <script src="../assets/js/theme-bootstrap.js"></script>
    <link rel="stylesheet" href="../assets/css/customer-auth-v3.css">
    <link rel="stylesheet" href="../assets/css/accessibility-preferences.css">
    <link rel="stylesheet" href="../assets/css/global-theme-system.css?v=083">
</head>
<body class="auth-v3">
<main class="auth-form-side">
    <div class="auth-card">
        <p class="auth-kicker">Email verification</p>
        <h2><?= $verified ? 'Verified.' : 'Unable to verify.' ?></h2>
        <div class="auth-alert <?= $verified ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <p class="auth-switch"><a href="login.php">Continue to sign in</a></p>
    </div>
</main>
</body>
</html>
