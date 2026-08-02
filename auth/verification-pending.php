<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/language.php';
require_once '../includes/registration-security.php';

$userId = (int) ($_SESSION['pending_verification_user_id'] ?? 0);
$email = strtolower(trim((string) ($_SESSION['pending_verification_email'] ?? '')));
$fullname = trim((string) ($_SESSION['pending_verification_name'] ?? 'Customer'));

if ($userId < 1 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: register.php');
    exit;
}

if (empty($_SESSION['verification_resend_csrf'])) {
    $_SESSION['verification_resend_csrf'] = bin2hex(random_bytes(32));
}

$message = 'Enter the six-digit code sent to your email address.';
$error = !empty($_SESSION['verification_delivery_error'])
    ? 'The verification email could not be sent. Check the private Brevo configuration, then use Resend code.'
    : '';
unset($_SESSION['verification_delivery_error']);
$verified = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = (string) ($_POST['csrf_token'] ?? '');
    $action = (string) ($_POST['action'] ?? 'verify');

    if (!hash_equals((string) $_SESSION['verification_resend_csrf'], $csrf)) {
        $error = 'Your session expired. Refresh the page and try again.';
    } elseif ($action === 'verify') {
        $code = trim((string) ($_POST['verification_code'] ?? ''));
        if (!preg_match('/^\d{6}$/', $code)) {
            $error = 'Enter the complete six-digit verification code.';
        } else {
            $maxAttempts = max(3, (int) (registrationSecurityConfig()['verification_code_max_attempts'] ?? 5));
            $statement = $pdo->prepare(
                'SELECT id, verification_code_hash, attempt_count
                 FROM email_verification_tokens
                 WHERE user_id = ? AND used_at IS NULL AND expires_at > NOW()
                   AND verification_code_hash IS NOT NULL
                 ORDER BY id DESC LIMIT 1'
            );
            $statement->execute([$userId]);
            $challenge = $statement->fetch(PDO::FETCH_ASSOC);

            if (!$challenge || (int) $challenge['attempt_count'] >= $maxAttempts) {
                $error = 'This code has expired or reached its attempt limit. Request a new code.';
            } elseif (!hash_equals((string) $challenge['verification_code_hash'], hash('sha256', $code))) {
                $pdo->prepare(
                    'UPDATE email_verification_tokens SET attempt_count = attempt_count + 1 WHERE id = ?'
                )->execute([(int) $challenge['id']]);
                $remaining = max(0, $maxAttempts - ((int) $challenge['attempt_count'] + 1));
                $error = $remaining > 0
                    ? 'That code is incorrect. ' . $remaining . ' attempt(s) remaining.'
                    : 'That code is incorrect. Request a new code to try again.';
            } else {
                try {
                    $pdo->beginTransaction();
                    $pdo->prepare(
                        'UPDATE customer_profiles
                         SET email_verified_at = NOW(), email_verification_token = NULL
                         WHERE user_id = ?'
                    )->execute([$userId]);
                    $pdo->prepare(
                        'UPDATE email_verification_tokens SET used_at = NOW()
                         WHERE user_id = ? AND used_at IS NULL'
                    )->execute([$userId]);
                    registrationSecurityRecord($pdo, 'email_verified', $email, $userId);
                    $pdo->commit();
                    $verified = true;
                    $message = 'Your email is verified. You can now sign in securely.';
                    unset(
                        $_SESSION['pending_verification_user_id'],
                        $_SESSION['pending_verification_email'],
                        $_SESSION['pending_verification_name'],
                        $_SESSION['development_verification_code'],
                        $_SESSION['development_verification_url']
                    );
                } catch (Throwable $exception) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $error = 'Verification could not be completed. Please try again.';
                }
            }
        }
    } elseif ($action === 'resend') {
        $config = registrationSecurityConfig();
        $hourLimit = (int) ($config['verification_resend_limit_per_hour'] ?? 3);
        $cooldown = (int) ($config['verification_resend_cooldown_seconds'] ?? 60);
        $latest = $pdo->prepare(
            "SELECT TIMESTAMPDIFF(SECOND, MAX(created_at), NOW())
             FROM registration_security_events
             WHERE event_type = 'verification_sent' AND user_id = ?"
        );
        $latest->execute([$userId]);
        $secondsSinceLast = $latest->fetchColumn();

        if (registrationSecurityCount($pdo, 'verification_sent', $email) >= $hourLimit) {
            $error = 'Too many verification requests. Please try again later.';
        } elseif ($secondsSinceLast !== null && (int) $secondsSinceLast < $cooldown) {
            $error = 'Please wait before requesting another verification code.';
        } else {
            $challenge = registrationSecurityIssueEmailChallenge($pdo, $userId);
            $url = registrationSecurityVerificationUrl($challenge['token']);
            $sent = registrationSecuritySendVerification($email, $fullname, $url, $challenge['code']);
            registrationSecurityRecord($pdo, 'verification_sent', $email, $userId);
            if ($sent) {
                $message = 'A new six-digit code was sent to your email address.';
                $error = '';
            } else {
                $error = 'The verification email could not be sent. Check the private Brevo configuration and try again.';
            }
        }
    }
}

function maskVerificationEmail(string $email): string
{
    [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
    $visible = substr($local, 0, min(2, strlen($local)));
    return $visible . str_repeat('*', max(3, strlen($local) - strlen($visible))) . '@' . $domain;
}
?>
<!doctype html>
<html lang="<?= htmlspecialchars($languageCode, ENT_QUOTES, 'UTF-8') ?>" data-theme="<?= htmlspecialchars($themeMode, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Email | NBW</title>
    <script src="../assets/js/theme-bootstrap.js"></script>
    <link rel="stylesheet" href="../assets/css/customer-auth-v3.css">
    <link rel="stylesheet" href="../assets/css/accessibility-preferences.css">
    <link rel="stylesheet" href="../assets/css/global-theme-system.css?v=083">
    <style>
        .otp-verification-page{min-height:100vh;margin:0;display:grid;place-items:center;padding:24px;box-sizing:border-box}
        .otp-verification-shell{width:min(100%,620px);margin:0;padding:0;display:block}
        .otp-verification-card{width:100%;box-sizing:border-box;margin:0;padding:46px 44px;border-radius:28px}
        .otp-verification-card h2{margin:8px 0 18px;line-height:1;font-size:clamp(3rem,8vw,4.3rem)}
        .otp-verification-card .auth-alert{margin:0 0 18px;padding:14px 16px;border-radius:12px;line-height:1.45}
        .otp-instruction{margin:0 0 18px;line-height:1.55;overflow-wrap:anywhere}
        .otp-form{display:grid;gap:10px;margin:0}
        .otp-form label{margin:0;font-weight:700}
        .otp-code{width:100%;height:72px;box-sizing:border-box;text-align:center;font-size:2rem;font-weight:700;letter-spacing:.55rem;padding:0 1rem;border:1px solid var(--border-color,#d8b56a);border-radius:14px;background:var(--surface,#fff);color:var(--text-primary,#2a1a12)}
        .otp-verification-card .auth-submit{width:100%;min-height:52px;margin:6px 0 0;display:flex;align-items:center;justify-content:center;box-sizing:border-box;text-decoration:none}
        .otp-actions{margin:14px 0 0}.otp-resend{background:transparent!important;border:1px solid var(--border-color,#d8b56a);box-shadow:none!important;color:var(--text-primary,#2a1a12)!important}
        .otp-verification-card .auth-switch{margin:18px 0 0;text-align:center}
        @media(max-width:640px){.otp-verification-page{padding:14px}.otp-verification-card{padding:34px 24px}.otp-code{height:64px}}
    </style>
</head>
<body class="auth-v3 otp-verification-page">
<main class="otp-verification-shell">
    <div class="auth-card otp-verification-card">
        <p class="auth-kicker">Protecting your account</p>
        <h2><?= $verified ? 'Email verified.' : 'Enter your code.' ?></h2>
        <?php if ($error !== ''): ?>
            <div class="auth-alert error"><?= htmlspecialchars($error) ?></div>
        <?php else: ?>
            <div class="auth-alert success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($verified): ?>
            <a class="auth-submit" href="login.php">Continue to sign in</a>
        <?php else: ?>
            <p class="otp-instruction">Code sent to <strong><?= htmlspecialchars(maskVerificationEmail($email)) ?></strong>. It expires in 10 minutes.</p>
            <?php if (registrationSecurityIsLocalRequest() && !empty($_SESSION['development_verification_code'])): ?>
                <div class="dev-reset"><strong>Local test code:</strong> <?= htmlspecialchars((string) $_SESSION['development_verification_code']) ?></div>
            <?php endif; ?>
            <form class="otp-form" method="post" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $_SESSION['verification_resend_csrf']) ?>">
                <input type="hidden" name="action" value="verify">
                <label for="verification_code">Six-digit verification code</label>
                <input class="otp-code" id="verification_code" name="verification_code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required autofocus>
                <button class="auth-submit" type="submit">Verify email</button>
            </form>
            <form class="otp-actions" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $_SESSION['verification_resend_csrf']) ?>">
                <input type="hidden" name="action" value="resend">
                <button class="auth-submit otp-resend" type="submit">Resend code</button>
            </form>
            <p class="auth-switch"><a href="login.php">Return to sign in</a></p>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
