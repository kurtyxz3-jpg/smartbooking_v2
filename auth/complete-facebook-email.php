<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../config/oauth.php';
require_once '../includes/language.php';

$pending = $_SESSION['pending_facebook_registration'] ?? null;

if (
    !is_array($pending)
    || empty($pending['provider_user_id'])
    || empty($pending['created_at'])
    || (time() - (int) $pending['created_at']) > 600
) {
    unset($_SESSION['pending_facebook_registration']);
    $_SESSION['social_login_error'] =
        'Your Facebook registration session expired. Please try again.';
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['facebook_email_csrf'])) {
    $_SESSION['facebook_email_csrf'] = bin2hex(random_bytes(32));
}

$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = (string) ($_POST['csrf_token'] ?? '');
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $acceptTerms = isset($_POST['accept_terms']);

    if (!hash_equals($_SESSION['facebook_email_csrf'], $csrfToken)) {
        $errors[] = 'Your form session expired. Refresh and try again.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (!$acceptTerms) {
        $errors[] = 'You must agree to the Terms of Use and Privacy Notice.';
    }

    if (!$errors) {
        $existingUser = $pdo->prepare(
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );
        $existingUser->execute([$email]);

        if ($existingUser->fetch()) {
            $errors[] =
                'That email already belongs to a SmartBooking account. '
                . 'Sign in to that account instead of creating another one.';
        }
    }

    if (!$errors) {
        $fullname = trim((string) ($pending['fullname'] ?? ''));
        $firstName = trim((string) ($pending['first_name'] ?? ''));
        $lastName = trim((string) ($pending['last_name'] ?? ''));

        if ($firstName === '') {
            $parts = preg_split('/\s+/', $fullname);
            $firstName = (string) ($parts[0] ?? 'Customer');
        }

        if ($lastName === '') {
            $parts = preg_split('/\s+/', $fullname);
            $lastName = count($parts) > 1
                ? (string) end($parts)
                : '';
        }

        if ($fullname === '') {
            $fullname = trim($firstName . ' ' . $lastName);
        }

        $username = oauthCreateUniqueUsername(
            $pdo,
            $firstName,
            $lastName,
            $email
        );
        $randomPassword = bin2hex(random_bytes(32));
        $verificationToken = bin2hex(random_bytes(32));

        $pdo->beginTransaction();

        try {
            $insertUser = $pdo->prepare(
                "INSERT INTO users (
                    fullname,
                    username,
                    email,
                    phone_number,
                    password,
                    role,
                    status
                ) VALUES (?, ?, ?, NULL, ?, 'customer', 'active')"
            );
            $insertUser->execute([
                $fullname,
                $username,
                $email,
                password_hash($randomPassword, PASSWORD_DEFAULT),
            ]);

            $userId = (int) $pdo->lastInsertId();

            $insertProfile = $pdo->prepare(
                "INSERT INTO customer_profiles (
                    user_id,
                    first_name,
                    middle_name,
                    last_name,
                    preferred_language,
                    registration_method,
                    email_verification_token,
                    email_verified_at
                ) VALUES (?, ?, NULL, ?, 'english', 'facebook', ?, NULL)"
            );
            $insertProfile->execute([
                $userId,
                $firstName,
                $lastName,
                $verificationToken,
            ]);

            $linkSocial = $pdo->prepare(
                "INSERT INTO social_accounts (
                    user_id,
                    provider,
                    provider_user_id,
                    provider_email
                ) VALUES (?, 'facebook', ?, ?)"
            );
            $linkSocial->execute([
                $userId,
                (string) $pending['provider_user_id'],
                $email,
            ]);

            $pdo->commit();

            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['fullname'] = $fullname;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = 'customer';
            $_SESSION['social_provider'] = 'facebook';
            $_SESSION['oauth_profile_return_to'] = !empty(
                $_SESSION['booking_draft']
            )
                ? '../customer/book.php?restore=1'
                : '../customer/dashboard.php';

            unset(
                $_SESSION['pending_facebook_registration'],
                $_SESSION['facebook_email_csrf']
            );

            header('Location: complete-social-profile.php');
            exit;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errors[] =
                'Facebook registration could not be completed. '
                . 'Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($languageCode, ENT_QUOTES, 'UTF-8') ?>" data-theme="<?= htmlspecialchars($themeMode, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facebook Email | Nails &amp; Body Worx</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/theme-bootstrap.js"></script>
    <link rel="stylesheet" href="../assets/css/customer-auth-v2.css?v=085">
    <link rel="stylesheet" href="../assets/css/accessibility-preferences.css">
    <link rel="icon" type="image/png" href="../assets/images/branding/nbw-favicon.png">
    <link rel="stylesheet" href="../assets/css/official-logo.css">
    <link rel="stylesheet" href="../assets/css/global-theme-system.css?v=085">
</head>
<body class="customer-auth-body">
<main class="customer-auth-layout">
    <section class="customer-auth-showcase">
        <a class="customer-auth-brand" href="../index.php">
            <img class="nbw-official-logo auth-brand-logo" src="../assets/images/branding/nbw-official-logo.webp" alt="Nails and Body Worx official logo">
            <div>
                <strong>Nails &amp; Body Worx</strong>
                <small>SmartBooking</small>
            </div>
        </a>

        <div class="customer-auth-showcase-copy">
            <p class="customer-auth-kicker">Facebook connected</p>
            <h1>Let’s finish creating your account.</h1>
            <p>
                Facebook verified your identity but did not share an email
                address. Enter the email SmartBooking should use for bookings
                and account notices.
            </p>
        </div>
    </section>

    <section class="customer-auth-form-section">
        <div class="customer-auth-form-shell">
            <div class="customer-auth-form-heading">
                <p class="customer-auth-kicker">Contact information</p>
                <h2>Add your email.</h2>
                <p>You will complete your username and other details next.</p>
            </div>

            <?php if ($errors): ?>
                <div class="customer-auth-alert customer-auth-alert-error">
                    <strong>Please review the following:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="on">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['facebook_email_csrf'], ENT_QUOTES, 'UTF-8') ?>">

                <div class="customer-auth-field">
                    <label for="email">Email Address</label>
                    <input id="email" name="email" type="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="email" required autofocus>
                </div>

                <label class="customer-auth-checkbox">
                    <input type="checkbox" name="accept_terms" required>
                    <span>I agree to the Terms of Use and Privacy Notice.</span>
                </label>

                <button class="customer-auth-submit" type="submit">
                    Continue profile setup <span>&rarr;</span>
                </button>
            </form>

            <p class="customer-auth-switch">
                Prefer another method? <a href="login.php">Return to sign in</a>
            </p>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/display-preferences-widget.php'; ?>
<script src="../assets/js/customer-auth-v2.js"></script>
<script src="../assets/js/accessibility-preferences.js"></script>
</body>
</html>
