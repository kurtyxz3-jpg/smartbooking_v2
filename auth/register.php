<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../config/oauth.php';
require_once '../includes/language.php';

if (!empty($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? '';

    if ($role === 'customer') {
        header('Location: ../customer/dashboard.php');
        exit;
    }
}

$errors = [];
$success = '';

$firstName = '';
$middleName = '';
$lastName = '';
$email = '';
$phoneNumber = '';
$birthday = '';
$gender = '';
$preferredLanguage = 'english';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $middleName = trim($_POST['middle_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $preferredLanguage = trim($_POST['preferred_language'] ?? 'english');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $acceptTerms = isset($_POST['accept_terms']);

    if ($firstName === '') {
        $errors[] = 'First name is required.';
    }

    if ($lastName === '') {
        $errors[] = 'Last name is required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($phoneNumber === '') {
        $errors[] = 'Mobile number is required.';
    }

    if ($birthday !== '') {
        $birthdayObject = DateTime::createFromFormat('Y-m-d', $birthday);

        if (!$birthdayObject || $birthdayObject->format('Y-m-d') !== $birthday) {
            $errors[] = 'Please enter a valid birthday.';
        } elseif ($birthdayObject > new DateTime('today')) {
            $errors[] = 'Birthday cannot be in the future.';
        }
    }

    if (!in_array($gender, ['', 'female', 'male', 'non_binary', 'prefer_not_to_say'], true)) {
        $errors[] = 'Invalid gender selection.';
    }

    if (!in_array($preferredLanguage, ['english', 'filipino', 'taglish'], true)) {
        $preferredLanguage = 'english';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must contain at least 8 characters.';
    }

    if (
        $password !== '' &&
        !preg_match('/[A-Z]/', $password)
    ) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }

    if (
        $password !== '' &&
        !preg_match('/[a-z]/', $password)
    ) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    }

    if (
        $password !== '' &&
        !preg_match('/[0-9]/', $password)
    ) {
        $errors[] = 'Password must contain at least one number.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$acceptTerms) {
        $errors[] = 'You must agree to the Terms and Privacy Notice.';
    }

    if (!$errors) {
        $check = $pdo->prepare(
            "SELECT id
             FROM users
             WHERE email = ?
             LIMIT 1"
        );
        $check->execute([$email]);

        if ($check->fetch()) {
            $errors[] = 'An account with this email address already exists.';
        }
    }

    if (!$errors) {
        $fullname = trim(
            $firstName
            . ' '
            . ($middleName !== '' ? $middleName . ' ' : '')
            . $lastName
        );

        $usernameBase = preg_replace(
            '/[^a-z0-9]/',
            '',
            strtolower($firstName . $lastName)
        );

        if ($usernameBase === '') {
            $usernameBase = 'customer';
        }

        $username = $usernameBase;
        $suffix = 1;

        while (true) {
            $usernameCheck = $pdo->prepare(
                "SELECT id
                 FROM users
                 WHERE username = ?
                 LIMIT 1"
            );
            $usernameCheck->execute([$username]);

            if (!$usernameCheck->fetch()) {
                break;
            }

            $username = $usernameBase . $suffix;
            $suffix++;
        }

        $verificationToken = bin2hex(random_bytes(32));

        $pdo->beginTransaction();

        try {
            $userStatement = $pdo->prepare(
                "INSERT INTO users (
                    fullname,
                    username,
                    email,
                    phone_number,
                    password,
                    role,
                    status
                ) VALUES (
                    :fullname,
                    :username,
                    :email,
                    :phone_number,
                    :password,
                    'customer',
                    'active'
                )"
            );

            $userStatement->execute([
                'fullname' => $fullname,
                'username' => $username,
                'email' => $email,
                'phone_number' => $phoneNumber,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            $userId = (int) $pdo->lastInsertId();

            $profileStatement = $pdo->prepare(
                "INSERT INTO customer_profiles (
                    user_id,
                    first_name,
                    middle_name,
                    last_name,
                    birthday,
                    gender,
                    preferred_language,
                    registration_method,
                    email_verification_token
                ) VALUES (
                    :user_id,
                    :first_name,
                    :middle_name,
                    :last_name,
                    :birthday,
                    :gender,
                    :preferred_language,
                    'personal_info',
                    :email_verification_token
                )"
            );

            $profileStatement->execute([
                'user_id' => $userId,
                'first_name' => $firstName,
                'middle_name' => $middleName !== '' ? $middleName : null,
                'last_name' => $lastName,
                'birthday' => $birthday !== '' ? $birthday : null,
                'gender' => $gender !== '' ? $gender : null,
                'preferred_language' => $preferredLanguage,
                'email_verification_token' => $verificationToken,
            ]);

            $languageStatement = $pdo->prepare(
                "INSERT INTO user_language_preferences (
                    user_id,
                    language_code
                ) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE
                    language_code = VALUES(language_code)"
            );
            $languageStatement->execute([$userId, $preferredLanguage]);

            $pdo->commit();

            $_SESSION['user_id'] = $userId;
            $_SESSION['fullname'] = $fullname;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = 'customer';

            header('Location: ../customer/dashboard.php');
            exit;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errors[] = 'Registration could not be completed. Please try again.';
        }
    }
}

$googleEnabled = oauthProviderEnabled('google');
$facebookEnabled = oauthProviderEnabled('facebook');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($languageCode, ENT_QUOTES, 'UTF-8') ?>" data-theme="<?= htmlspecialchars($themeMode, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account | Nails &amp; Body Worx</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/theme-bootstrap.js"></script>
    <link rel="stylesheet" href="../assets/css/customer-auth-v2.css">
    <link rel="stylesheet" href="../assets/css/accessibility-preferences.css">
    <link rel="icon" type="image/png" href="../assets/images/branding/nbw-favicon.png">
    <link rel="stylesheet" href="../assets/css/official-logo.css">
    <link rel="stylesheet" href="../assets/css/global-theme-system.css">
</head>

<body class="customer-auth-body">
<main class="customer-auth-layout">
    <section class="customer-auth-showcase">
        <a class="customer-auth-brand" href="../index.php">
            <img
    class="nbw-official-logo auth-brand-logo"
    src="../assets/images/branding/nbw-official-logo.webp"
    alt="Nails and Body Worx official logo"
>

            <div>
                <strong>Nails &amp; Body Worx</strong>
                <small>SmartBooking</small>
            </div>
        </a>

        <div class="customer-auth-showcase-copy">
            <p class="customer-auth-kicker">Your personal beauty space</p>

            <h1>
                Create an account that remembers what you love.
            </h1>

            <p>
                Save your preferred services, manage appointments, create AI nail
                concepts, and return to your favorite looks in one place.
            </p>
        </div>

        <div class="customer-auth-preview">
            <div class="customer-auth-preview-card">
                <small>AI Nail Studio</small>
                <strong>Three personalized design directions</strong>

                <div class="customer-auth-nails">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>

            <div class="customer-auth-feature-list">
                <div><span>✓</span><p>Easy appointment management</p></div>
                <div><span>✓</span><p>Preferred staff and services</p></div>
                <div><span>✓</span><p>Design history and rebooking</p></div>
            </div>
        </div>
    </section>

    <section class="customer-auth-form-section">
        <div class="customer-auth-form-shell">
            <div class="customer-auth-form-heading">
                <p class="customer-auth-kicker">Join SmartBooking</p>
                <h2>Start your beauty journey.</h2>
                <p>Create your account using Google, Facebook, or your personal information.</p>
            </div>

            <?php if ($errors): ?>
                <div class="customer-auth-alert customer-auth-alert-error">
                    <strong>Please review the following:</strong>

                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li>
                                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="customer-social-grid">
                <a
                    class="customer-social-button <?= !$googleEnabled ? 'is-disabled' : '' ?>"
                    href="<?= $googleEnabled ? 'google-login.php' : '#' ?>"
                    <?= !$googleEnabled ? 'aria-disabled="true" data-social-disabled="Google"' : '' ?>
                >
                    <span class="social-google">G</span>
                    Continue with Google
                </a>

                <a
                    class="customer-social-button <?= !$facebookEnabled ? 'is-disabled' : '' ?>"
                    href="<?= $facebookEnabled ? 'facebook-login.php' : '#' ?>"
                    <?= !$facebookEnabled ? 'aria-disabled="true" data-social-disabled="Facebook"' : '' ?>
                >
                    <span class="social-facebook">f</span>
                    Continue with Facebook
                </a>
            </div>

            <div class="customer-auth-divider">
                <span>or create an account manually</span>
            </div>

            <form method="POST" autocomplete="on">
                <div class="customer-auth-grid">
                    <div class="customer-auth-field">
                        <label for="first_name">First Name</label>
                        <input
                            id="first_name"
                            name="first_name"
                            type="text"
                            value="<?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>"
                            autocomplete="given-name"
                            required
                        >
                    </div>

                    <div class="customer-auth-field">
                        <label for="middle_name">Middle Name <span>Optional</span></label>
                        <input
                            id="middle_name"
                            name="middle_name"
                            type="text"
                            value="<?= htmlspecialchars($middleName, ENT_QUOTES, 'UTF-8') ?>"
                            autocomplete="additional-name"
                        >
                    </div>

                    <div class="customer-auth-field customer-auth-span-2">
                        <label for="last_name">Last Name</label>
                        <input
                            id="last_name"
                            name="last_name"
                            type="text"
                            value="<?= htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8') ?>"
                            autocomplete="family-name"
                            required
                        >
                    </div>

                    <div class="customer-auth-field customer-auth-span-2">
                        <label for="email">Email Address</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="customer-auth-field">
                        <label for="phone_number">Mobile Number</label>
                        <input
                            id="phone_number"
                            name="phone_number"
                            type="tel"
                            value="<?= htmlspecialchars($phoneNumber, ENT_QUOTES, 'UTF-8') ?>"
                            autocomplete="tel"
                            placeholder="09XXXXXXXXX"
                            required
                        >
                    </div>

                    <div class="customer-auth-field">
                        <label for="birthday">Birthday <span>Optional</span></label>
                        <input
                            id="birthday"
                            name="birthday"
                            type="date"
                            value="<?= htmlspecialchars($birthday, ENT_QUOTES, 'UTF-8') ?>"
                            autocomplete="bday"
                        >
                    </div>

                    <div class="customer-auth-field">
                        <label for="gender">Gender <span>Optional</span></label>
                        <select id="gender" name="gender">
                            <option value="">Select an option</option>
                            <option value="female" <?= $gender === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="male" <?= $gender === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="non_binary" <?= $gender === 'non_binary' ? 'selected' : '' ?>>Non-binary</option>
                            <option value="prefer_not_to_say" <?= $gender === 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer not to say</option>
                        </select>
                    </div>

                    <div class="customer-auth-field">
                        <label for="preferred_language">Preferred Language</label>
                        <select id="preferred_language" name="preferred_language">
                            <option value="english" <?= $preferredLanguage === 'english' ? 'selected' : '' ?>>English</option>
                            <option value="filipino" <?= $preferredLanguage === 'filipino' ? 'selected' : '' ?>>Filipino</option>
                            <option value="taglish" <?= $preferredLanguage === 'taglish' ? 'selected' : '' ?>>Taglish</option>
                        </select>
                    </div>

                    <div class="customer-auth-field">
                        <label for="password">Password</label>

                        <div class="customer-password-wrap">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                type="button"
                                class="customer-password-toggle"
                                data-password-toggle="password"
                                aria-label="Show password"
                            >
                                Show
                            </button>
                        </div>
                    </div>

                    <div class="customer-auth-field">
                        <label for="confirm_password">Confirm Password</label>

                        <div class="customer-password-wrap">
                            <input
                                id="confirm_password"
                                name="confirm_password"
                                type="password"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                type="button"
                                class="customer-password-toggle"
                                data-password-toggle="confirm_password"
                                aria-label="Show password"
                            >
                                Show
                            </button>
                        </div>
                    </div>
                </div>

                <div class="customer-password-requirements">
                    <span>Use at least 8 characters</span>
                    <span>Include uppercase and lowercase letters</span>
                    <span>Include at least one number</span>
                </div>

                <label class="customer-auth-checkbox">
                    <input type="checkbox" name="accept_terms" required>

                    <span>
                        I agree to the
                        <a href="#" data-modal-open="termsModal">Terms of Use</a>
                        and
                        <a href="#" data-modal-open="privacyModal">Privacy Notice</a>.
                    </span>
                </label>

                <button class="customer-auth-submit" type="submit">
                    Create my account
                    <span>↗</span>
                </button>
            </form>

            <p class="customer-auth-switch">
                Already have an account?
                <a href="login.php">Sign in</a>
            </p>
        </div>
    </section>
</main>

<div class="customer-auth-modal" id="termsModal" aria-hidden="true">
    <div class="customer-auth-modal-card">
        <button type="button" data-modal-close aria-label="Close">×</button>
        <p class="customer-auth-kicker">Terms of Use</p>
        <h2>SmartBooking account terms</h2>
        <p>
            Customers are responsible for providing accurate account and booking
            information. Appointment confirmation remains subject to availability
            and payment verification.
        </p>
    </div>
</div>

<div class="customer-auth-modal" id="privacyModal" aria-hidden="true">
    <div class="customer-auth-modal-card">
        <button type="button" data-modal-close aria-label="Close">×</button>
        <p class="customer-auth-kicker">Privacy Notice</p>
        <h2>How account information is used</h2>
        <p>
            Personal information is used for account management, appointments,
            service personalization, notifications, payment review, and customer support.
        </p>
    </div>
</div>

<?php include __DIR__ . '/../includes/display-preferences-widget.php'; ?>
<script src="../assets/js/customer-auth-v2.js"></script>
<script src="../assets/js/accessibility-preferences.js"></script>
</body>
</html>
