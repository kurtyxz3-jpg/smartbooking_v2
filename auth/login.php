<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/oauth.php';
require_once __DIR__ . '/../includes/language.php';
require_once __DIR__ . '/../includes/branch-context.php';
require_once __DIR__ . '/../includes/registration-security.php';

$redirects = [
    'super_admin' => '../superadmin/dashboard.php',
    'admin' => '../admin/dashboard.php',
    'staff' => '../staff/dashboard.php',
    'customer' => '../customer/dashboard.php',
];

if (
    isset($_SESSION['user_id'], $_SESSION['role'])
    && isset($redirects[$_SESSION['role']])
) {
    if (
        $_SESSION['role'] === 'customer'
        && !registrationSecurityManualEmailVerified($pdo, (int) $_SESSION['user_id'])
    ) {
        registrationSecurityRequirePendingVerification($pdo, (int) $_SESSION['user_id']);
        header('Location: verification-pending.php');
        exit;
    }
    header('Location: ' . $redirects[$_SESSION['role']]);
    exit;
}

$ui = [
    'english' => [
        'showcase_title' => 'Your beauty space is ready when you are.',
        'showcase_text' => 'Manage bookings, revisit favorite services, save styling ideas, and keep your salon experience in one place.',
        'benefit_1' => 'Real-time booking management',
        'benefit_2' => 'AI nail design references',
        'benefit_3' => 'Personalized customer history',
        'secure' => 'Secure account access',
        'title' => 'Sign in.',
        'subtitle' => 'Use your username or email address.',
        'google' => 'Continue with Google',
        'facebook' => 'Continue with Facebook',
        'manual' => 'or sign in manually',
        'login_label' => 'Username or Email',
        'password' => 'Password',
        'show' => 'Show',
        'remember' => 'Remember this device',
        'forgot' => 'Forgot password?',
        'submit' => 'Sign in securely ↗',
        'new' => 'New to SmartBooking?',
        'create' => 'Create a customer account',
        'return' => '← Return to Home page',
        'required' => 'Please enter your username or email and password.',
        'invalid' => 'Invalid username/email or password.',
        'inactive' => 'Your account is inactive. Please contact the system administrator.',
        'unsupported' => 'This account role is not supported.',
    ],
    'filipino' => [
        'welcome' => 'Maligayang pagbabalik',
        'showcase_title' => 'Handa ang iyong beauty space anumang oras.',
        'showcase_text' => 'Pamahalaan ang bookings, balikan ang paboritong serbisyo, i-save ang styling ideas, at panatilihin sa iisang lugar ang iyong salon experience.',
        'benefit_1' => 'Real-time na pamamahala ng booking',
        'benefit_2' => 'Mga AI nail design reference',
        'benefit_3' => 'Personalized na customer history',
        'secure' => 'Ligtas na pag-access sa account',
        'title' => 'Mag-sign in.',
        'subtitle' => 'Gamitin ang iyong username o email address.',
        'google' => 'Magpatuloy gamit ang Google',
        'facebook' => 'Magpatuloy gamit ang Facebook',
        'manual' => 'o manual na mag-sign in',
        'login_label' => 'Username o Email',
        'password' => 'Password',
        'show' => 'Ipakita',
        'remember' => 'Tandaan ang device na ito',
        'forgot' => 'Nakalimutan ang password?',
        'submit' => 'Ligtas na mag-sign in ↗',
        'new' => 'Bago sa SmartBooking?',
        'create' => 'Gumawa ng customer account',
        'return' => '← Bumalik sa Home page',
        'required' => 'Ilagay ang iyong username o email at password.',
        'invalid' => 'Mali ang username/email o password.',
        'inactive' => 'Hindi aktibo ang iyong account. Makipag-ugnayan sa system administrator.',
        'unsupported' => 'Hindi suportado ang role ng account na ito.',
    ],
    'taglish' => [
        'showcase_title' => 'Ready ang beauty space mo whenever you are.',
        'showcase_text' => 'Manage your bookings, balikan ang favorite services, i-save ang styling ideas, at keep everything in one place.',
        'benefit_1' => 'Real-time booking management',
        'benefit_2' => 'AI nail design references',
        'benefit_3' => 'Personalized customer history',
        'secure' => 'Secure account access',
        'title' => 'Sign in.',
        'subtitle' => 'Gamitin ang username o email address mo.',
        'google' => 'Continue with Google',
        'facebook' => 'Continue with Facebook',
        'manual' => 'or sign in manually',
        'login_label' => 'Username or Email',
        'password' => 'Password',
        'show' => 'Show',
        'remember' => 'Remember this device',
        'forgot' => 'Forgot password?',
        'submit' => 'Sign in securely ↗',
        'new' => 'New sa SmartBooking?',
        'create' => 'Create a customer account',
        'return' => '← Return sa Home page',
        'required' => 'I-enter ang username or email at password.',
        'invalid' => 'Invalid ang username/email or password.',
        'inactive' => 'Inactive ang account mo. Please contact the system administrator.',
        'unsupported' => 'Hindi supported ang account role na ito.',
    ],
];

$labels = $ui[$languageCode] ?? $ui['english'];
$error = trim((string) ($_SESSION['social_login_error'] ?? ''));
unset($_SESSION['social_login_error']);
$loginValue = '';
$notice = $_SESSION['login_notice'] ?? '';
unset($_SESSION['login_notice']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginValue = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($loginValue === '' || $password === '') {
        $error = $labels['required'];
    } elseif (registrationSecurityCount($pdo, 'login_failure', $loginValue) >= (int) (registrationSecurityConfig()['login_failure_limit_per_hour'] ?? 10)) {
        $error = 'Too many sign-in attempts. Please wait before trying again.';
    } else {
        $branchSelect = branchColumnExists($pdo, 'users', 'branch_id')
            ? ', branch_id'
            : ', NULL AS branch_id';
        $statement = $pdo->prepare(
            "SELECT id, fullname, username, email, password, role, status {$branchSelect}
             FROM users
             WHERE username = :username OR email = :email
             LIMIT 1"
        );
        $statement->execute([
            'username' => $loginValue,
            'email' => $loginValue,
        ]);
        $user = $statement->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            registrationSecurityRecord($pdo, 'login_failure', $loginValue);
            $error = $labels['invalid'];
        } elseif ($user['status'] !== 'active') {
            $error = $labels['inactive'];
        } elseif (
            $user['role'] === 'customer'
            && !registrationSecurityManualEmailVerified($pdo, (int) $user['id'])
        ) {
            $_SESSION['pending_verification_user_id'] = (int) $user['id'];
            $_SESSION['pending_verification_email'] = (string) $user['email'];
            $_SESSION['pending_verification_name'] = (string) $user['fullname'];
            header('Location: verification-pending.php');
            exit;
        } elseif (!isset($redirects[$user['role']])) {
            $error = $labels['unsupported'];
        } else {
            registrationSecurityRecord($pdo, 'login_success', $loginValue, (int) $user['id']);
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $user['id'];
    require_once __DIR__ . '/../includes/session-security.php';
    sessionSecurityCapture($pdo, (string)$user['password']);
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['branch_id'] = salonBranchId($pdo);

            if (function_exists('accountCenterLog')) {
                accountCenterLog(
                    $pdo,
                    (int) $user['id'],
                    'login',
                    'success'
                );
            }

            $destination =
                $user['role'] === 'customer'
                && !empty($_SESSION['booking_draft'])
                    ? '../customer/book.php?restore=1'
                    : $redirects[$user['role']];

            header('Location: ' . $destination);
            exit;
        }
    }
}

$google = oauthProviderEnabled('google');
$facebook = oauthProviderEnabled('facebook');
?>
<!doctype html>
<html
    lang="<?= htmlspecialchars($languageCode, ENT_QUOTES, 'UTF-8') ?>"
    data-theme="<?= htmlspecialchars($themeMode, ENT_QUOTES, 'UTF-8') ?>"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= htmlspecialchars($labels['title'], ENT_QUOTES, 'UTF-8') ?> | NBW</title>

    <script src="../assets/js/theme-bootstrap.js"></script>
    <link rel="stylesheet" href="../assets/css/customer-auth-v3.css?v=085">
    <link rel="stylesheet" href="../assets/css/accessibility-preferences.css">
    <link rel="icon" type="image/png" href="../assets/images/branding/nbw-favicon.png">
    <link rel="stylesheet" href="../assets/css/official-logo.css">
    <link rel="stylesheet" href="../assets/css/global-theme-system.css?v=085">
</head>

<body class="auth-v3">
<main class="auth-layout">
    <section class="auth-showcase">
        <a class="auth-brand" href="../index.php">
            <img
    class="nbw-official-logo auth-brand-logo"
    src="../assets/images/branding/nbw-official-logo.webp"
    alt="Nails and Body Worx official logo"
>
            <div>
                <strong>Nails &amp; Body Worx</strong>
            </div>
        </a>

        <div class="auth-copy">
            <h1>
                <?= htmlspecialchars($labels['showcase_title'], ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <p>
                <?= htmlspecialchars($labels['showcase_text'], ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>

        <div class="auth-benefits">
            <div><strong>01</strong><span><?= htmlspecialchars($labels['benefit_1'], ENT_QUOTES, 'UTF-8') ?></span></div>
            <div><strong>02</strong><span><?= htmlspecialchars($labels['benefit_2'], ENT_QUOTES, 'UTF-8') ?></span></div>
            <div><strong>03</strong><span><?= htmlspecialchars($labels['benefit_3'], ENT_QUOTES, 'UTF-8') ?></span></div>
        </div>
    </section>

    <section class="auth-form-side">
        <div class="auth-card">
            <p class="auth-kicker"><?= htmlspecialchars($labels['secure'], ENT_QUOTES, 'UTF-8') ?></p>
            <h2><?= htmlspecialchars($labels['title'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars($labels['subtitle'], ENT_QUOTES, 'UTF-8') ?></p>

            <?php if ($notice): ?>
                <div class="auth-alert success">
                    <?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="auth-alert error">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <div class="auth-socials">
                <a
                    class="auth-social <?= $google ? '' : 'disabled' ?>"
                    href="<?= $google ? 'google-login.php?intent=login' : '#' ?>"
                    <?= $google ? '' : 'data-disabled-provider="Google"' ?>
                >
                    G &nbsp; <?= htmlspecialchars($labels['google'], ENT_QUOTES, 'UTF-8') ?>
                </a>

                <a
                    class="auth-social <?= $facebook ? '' : 'disabled' ?>"
                    href="<?= $facebook ? 'facebook-login.php?intent=login' : '#' ?>"
                    <?= $facebook ? '' : 'data-disabled-provider="Facebook"' ?>
                >
                    f &nbsp; <?= htmlspecialchars($labels['facebook'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            </div>

            <div class="auth-divider">
                <?= htmlspecialchars($labels['manual'], ENT_QUOTES, 'UTF-8') ?>
            </div>

            <form method="post">
                <div class="auth-field">
                    <label for="login">
                        <?= htmlspecialchars($labels['login_label'], ENT_QUOTES, 'UTF-8') ?>
                    </label>
                    <input
                        id="login"
                        name="login"
                        value="<?= htmlspecialchars($loginValue, ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="auth-field">
                    <label for="password">
                        <?= htmlspecialchars($labels['password'], ENT_QUOTES, 'UTF-8') ?>
                    </label>

                    <div class="auth-password">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" data-password="password">
                            <?= htmlspecialchars($labels['show'], ENT_QUOTES, 'UTF-8') ?>
                        </button>
                    </div>
                </div>

                <div class="auth-row">
                    <label>
                        <input type="checkbox" name="remember_login">
                        <?= htmlspecialchars($labels['remember'], ENT_QUOTES, 'UTF-8') ?>
                    </label>

                    <a href="forgot-password.php">
                        <?= htmlspecialchars($labels['forgot'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </div>

                <button class="auth-submit">
                    <?= htmlspecialchars($labels['submit'], ENT_QUOTES, 'UTF-8') ?>
                </button>
            </form>

            <p class="auth-switch">
                <?= htmlspecialchars($labels['new'], ENT_QUOTES, 'UTF-8') ?>
                <a href="register.php">
                    <?= htmlspecialchars($labels['create'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            </p>

            <a class="auth-back" href="../index.php">
                <?= htmlspecialchars($labels['return'], ENT_QUOTES, 'UTF-8') ?>
            </a>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/display-preferences-widget.php'; ?>

<script src="../assets/js/customer-auth-v3.js"></script>
<script src="../assets/js/accessibility-preferences.js"></script>
</body>
</html>
