<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../config/oauth.php';
require_once '../includes/language.php';
require_once '../includes/registration-security.php';
require_once '../includes/turnstile.php';

$pendingSocial = $_SESSION['pending_social_registration'] ?? null;
$hasPendingSocial = is_array($pendingSocial) && !empty($pendingSocial['provider_user_id']);
$hasExistingSocial = !empty($_SESSION['user_id'])
    && ($_SESSION['role'] ?? '') === 'customer'
    && !empty($_SESSION['social_provider']);

if (!$hasPendingSocial && !$hasExistingSocial) {
    header('Location: login.php');
    exit;
}

$userId = $hasExistingSocial ? (int) $_SESSION['user_id'] : 0;
$errors = [];

if (empty($_SESSION['complete_profile_csrf'])) {
    $_SESSION['complete_profile_csrf'] = bin2hex(random_bytes(32));
}
if (empty($_SESSION['cancel_social_csrf'])) {
    $_SESSION['cancel_social_csrf'] = bin2hex(random_bytes(32));
}

$profile = null;
if ($hasExistingSocial) {
    $profileStatement = $pdo->prepare(
    "SELECT
        u.username,
        u.email,
        u.phone_number,
        cp.first_name,
        cp.middle_name,
        cp.last_name,
        cp.birthday,
        cp.gender,
        cp.preferred_language
     FROM users u
     LEFT JOIN customer_profiles cp ON cp.user_id = u.id
     WHERE u.id = ?
       AND u.role = 'customer'
     LIMIT 1"
    );
    $profileStatement->execute([$userId]);
    $profile = $profileStatement->fetch(PDO::FETCH_ASSOC);
} else {
    $profile = [
        'username' => '',
        'email' => (string) ($pendingSocial['email'] ?? ''),
        'phone_number' => '',
        'first_name' => (string) ($pendingSocial['first_name'] ?? ''),
        'middle_name' => '',
        'last_name' => (string) ($pendingSocial['last_name'] ?? ''),
        'birthday' => '',
        'gender' => '',
        'preferred_language' => 'english',
    ];
}

if (!$profile) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$username = trim((string) ($profile['username'] ?? ''));
$firstName = trim((string) ($profile['first_name'] ?? ''));
$middleName = trim((string) ($profile['middle_name'] ?? ''));
$lastName = trim((string) ($profile['last_name'] ?? ''));
$phoneNumber = trim((string) ($profile['phone_number'] ?? ''));
$birthday = trim((string) ($profile['birthday'] ?? ''));
$gender = trim((string) ($profile['gender'] ?? ''));
$preferredLanguage = trim(
    (string) ($profile['preferred_language'] ?? 'english')
);
$socialProvider = strtolower((string) (
    $hasPendingSocial
        ? ($pendingSocial['provider'] ?? '')
        : ($_SESSION['social_provider'] ?? '')
));
$storedEmail = strtolower(trim((string) ($profile['email'] ?? '')));
$hasRealContactEmail = $storedEmail !== ''
    && !str_ends_with($storedEmail, '@facebook.invalid');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = (string) ($_POST['csrf_token'] ?? '');

    if (!hash_equals($_SESSION['complete_profile_csrf'], $csrfToken)) {
        $errors[] = 'Your form session expired. Please refresh and try again.';
    }

    $username = trim((string) ($_POST['username'] ?? ''));
    $firstName = trim((string) ($_POST['first_name'] ?? ''));
    $middleName = trim((string) ($_POST['middle_name'] ?? ''));
    $lastName = trim((string) ($_POST['last_name'] ?? ''));
    $phoneNumber = trim((string) ($_POST['phone_number'] ?? ''));
    $birthday = trim((string) ($_POST['birthday'] ?? ''));
    $gender = trim((string) ($_POST['gender'] ?? ''));
    $preferredLanguage = trim(
        (string) ($_POST['preferred_language'] ?? 'english')
    );
    $acceptTerms = isset($_POST['accept_terms']);

    if (!preg_match('/^[A-Za-z0-9._]{3,40}$/', $username)) {
        $errors[] = 'Username must be 3–40 characters and may use letters, numbers, periods, and underscores.';
    }

    if ($firstName === '') {
        $errors[] = 'First name is required.';
    }

    if ($lastName === '') {
        $errors[] = 'Last name is required.';
    }

    $normalizedPhone = preg_replace('/[\s\-()]/', '', $phoneNumber);

    if (!preg_match('/^(?:09\d{9}|\+639\d{9})$/', $normalizedPhone)) {
        $errors[] = 'Enter a valid Philippine mobile number, such as 09171234567.';
    } else {
        $phoneNumber = $normalizedPhone;
    }

    if ($birthday !== '') {
        $birthdayObject = DateTime::createFromFormat('Y-m-d', $birthday);

        if (!$birthdayObject || $birthdayObject->format('Y-m-d') !== $birthday) {
            $errors[] = 'Please enter a valid birthday.';
        } elseif ($birthdayObject > new DateTime('today')) {
            $errors[] = 'Birthday cannot be in the future.';
        }
    }

    $validGenders = [
        '',
        'female',
        'male',
        'non_binary',
        'prefer_not_to_say',
    ];

    if (!in_array($gender, $validGenders, true)) {
        $errors[] = 'Invalid gender selection.';
    }

    if (
        !in_array(
            $preferredLanguage,
            ['english', 'filipino', 'taglish'],
            true
        )
    ) {
        $preferredLanguage = 'english';
    }

    if (!$acceptTerms) {
        $errors[] = 'You must agree to the Terms of Use and Privacy Notice.';
    }

    if (!turnstileValidate((string) ($_POST['cf-turnstile-response'] ?? ''), 'social_registration')) {
        registrationSecurityRecord($pdo, 'turnstile_failed', $storedEmail);
        $errors[] = 'Please complete the security check and try again.';
    }

    if (!$errors) {
        $phoneCheck = $hasPendingSocial
            ? $pdo->prepare("SELECT id FROM users WHERE phone_number = ? LIMIT 1")
            : $pdo->prepare("SELECT id FROM users WHERE phone_number = ? AND id <> ? LIMIT 1");
        $phoneCheck->execute($hasPendingSocial ? [$phoneNumber] : [$phoneNumber, $userId]);
        if ($phoneCheck->fetch()) {
            $errors[] = 'That mobile number is already connected to an account.';
        }
    }

    if (!$errors) {
        $usernameCheck = $hasPendingSocial
            ? $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1")
            : $pdo->prepare("SELECT id FROM users WHERE username = ? AND id <> ? LIMIT 1");
        $usernameCheck->execute(
            $hasPendingSocial ? [$username] : [$username, $userId]
        );

        if ($usernameCheck->fetch()) {
            $errors[] = 'That username is already taken. Please choose another.';
        }
    }

    if (!$errors) {
        $fullname = trim(
            $firstName
            . ' '
            . ($middleName !== '' ? $middleName . ' ' : '')
            . $lastName
        );

        try {
            if ($hasPendingSocial) {
                oauthCreateOrLoginCustomer(
                    $pdo,
                    (string) $pendingSocial['provider'],
                    (string) $pendingSocial['provider_user_id'],
                    (string) $pendingSocial['email'],
                    (string) ($pendingSocial['fullname'] ?? $fullname),
                    (string) ($pendingSocial['first_name'] ?? $firstName),
                    (string) ($pendingSocial['last_name'] ?? $lastName)
                );
                $userId = (int) $_SESSION['user_id'];
            }

            $pdo->beginTransaction();
            $updateUser = $pdo->prepare(
                "UPDATE users
                 SET fullname = ?, username = ?, phone_number = ?
                 WHERE id = ? AND role = 'customer'"
            );
            $updateUser->execute([
                $fullname,
                $username,
                $phoneNumber,
                $userId,
            ]);

            $updateProfile = $pdo->prepare(
                "UPDATE customer_profiles
                 SET first_name = ?,
                     middle_name = ?,
                     last_name = ?,
                     birthday = ?,
                     gender = ?,
                     preferred_language = ?
                 WHERE user_id = ?"
            );
            $updateProfile->execute([
                $firstName,
                $middleName !== '' ? $middleName : null,
                $lastName,
                $birthday !== '' ? $birthday : null,
                $gender !== '' ? $gender : null,
                $preferredLanguage,
                $userId,
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

            $_SESSION['fullname'] = $fullname;
            $_SESSION['username'] = $username;
            unset($_SESSION['pending_social_registration']);
            unset($_SESSION['complete_profile_csrf']);

            $destination = (string) (
                $_SESSION['oauth_profile_return_to']
                ?? '../customer/dashboard.php'
            );
            unset($_SESSION['oauth_profile_return_to']);

            if (!str_starts_with($destination, '../customer/')) {
                $destination = '../customer/dashboard.php';
            }

            header('Location: ' . $destination);
            exit;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errors[] = 'Your profile could not be saved. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($languageCode, ENT_QUOTES, 'UTF-8') ?>" data-theme="<?= htmlspecialchars($themeMode, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Profile | Nails &amp; Body Worx</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/theme-bootstrap.js"></script>
    <link rel="stylesheet" href="../assets/css/customer-auth-v2.css">
    <link rel="stylesheet" href="../assets/css/accessibility-preferences.css">
    <link rel="icon" type="image/png" href="../assets/images/branding/nbw-favicon.png">
    <link rel="stylesheet" href="../assets/css/official-logo.css">
    <link rel="stylesheet" href="../assets/css/global-theme-system.css?v=084">
    <style>
        .social-registration-exit{margin-top:18px;padding-top:18px;border-top:1px solid var(--border-color,#d8c8b8);display:grid;gap:10px;text-align:center}
        .social-registration-exit form{margin:0}
        .social-registration-cancel{width:100%;min-height:44px;border:1px solid var(--theme-border,#d8c8b8)!important;border-radius:999px;background:transparent!important;color:var(--theme-text,#2a1a12)!important;font:inherit;font-weight:700;cursor:pointer;box-shadow:none!important}
        .social-registration-signin{margin:0!important;color:var(--theme-muted,#6d5a50)!important;font-size:.82rem;line-height:1.4;text-align:center}
        .social-registration-signin button{display:inline!important;width:auto!important;min-height:0!important;margin:0!important;border:0!important;padding:0!important;background:none!important;box-shadow:none!important;color:var(--theme-accent,#b57b20)!important;font:inherit!important;font-weight:800!important;cursor:pointer;text-decoration:none!important}
    </style>
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
            <p class="customer-auth-kicker">One final step</p>
            <h1>Make your SmartBooking account yours.</h1>
            <p>
                Choose your preferred username and complete the details the salon
                needs for appointments and customer support.
            </p>
        </div>
    </section>

    <section class="customer-auth-form-section">
        <div class="customer-auth-form-shell">
            <div class="customer-auth-form-heading">
                <p class="customer-auth-kicker">Profile setup</p>
                <h2>Complete your profile.</h2>
                <?php if ($socialProvider === 'google'): ?>
                    <p>Your Google email is verified and cannot be changed here.</p>
                <?php elseif ($hasRealContactEmail): ?>
                    <p>Your Facebook email is connected and cannot be changed here.</p>
                <?php else: ?>
                    <p>Your Facebook account is connected. Complete the remaining details below.</p>
                <?php endif; ?>
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
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['complete_profile_csrf'], ENT_QUOTES, 'UTF-8') ?>">

                <div class="customer-auth-grid">
                    <div class="customer-auth-field customer-auth-span-2">
                        <label for="username">Preferred Username</label>
                        <input id="username" name="username" type="text" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" autocomplete="username" minlength="3" maxlength="40" pattern="[A-Za-z0-9._]+" required>
                    </div>

                    <div class="customer-auth-field">
                        <label for="first_name">First Name</label>
                        <input id="first_name" name="first_name" type="text" value="<?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>" autocomplete="given-name" required>
                    </div>

                    <div class="customer-auth-field">
                        <label for="middle_name">Middle Name <span>Optional</span></label>
                        <input id="middle_name" name="middle_name" type="text" value="<?= htmlspecialchars($middleName, ENT_QUOTES, 'UTF-8') ?>" autocomplete="additional-name">
                    </div>

                    <div class="customer-auth-field customer-auth-span-2">
                        <label for="last_name">Last Name</label>
                        <input id="last_name" name="last_name" type="text" value="<?= htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8') ?>" autocomplete="family-name" required>
                    </div>

                    <?php if ($hasRealContactEmail): ?>
                        <div class="customer-auth-field customer-auth-span-2">
                            <label for="email">
                                <?= $socialProvider === 'google'
                                    ? 'Verified Google Email'
                                    : 'Facebook Email' ?>
                            </label>
                            <input id="email" type="email" value="<?= htmlspecialchars((string) $profile['email'], ENT_QUOTES, 'UTF-8') ?>" readonly>
                        </div>
                    <?php endif; ?>

                    <div class="customer-auth-field">
                        <label for="phone_number">Mobile Number</label>
                        <input id="phone_number" name="phone_number" type="tel" value="<?= htmlspecialchars($phoneNumber, ENT_QUOTES, 'UTF-8') ?>" autocomplete="tel" placeholder="09XXXXXXXXX" required>
                    </div>

                    <div class="customer-auth-field">
                        <label for="birthday">Birthday <span>Optional</span></label>
                        <input id="birthday" name="birthday" type="date" value="<?= htmlspecialchars($birthday, ENT_QUOTES, 'UTF-8') ?>" autocomplete="bday">
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
                </div>

                <label class="customer-auth-checkbox">
                    <input type="checkbox" name="accept_terms" required>
                    <span>I agree to the Terms of Use and Privacy Notice.</span>
                </label>

                <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars(turnstileSiteKey(), ENT_QUOTES, 'UTF-8') ?>" data-action="social_registration"></div>

                <button class="customer-auth-submit" type="submit">
                    Save and continue <span>&rarr;</span>
                </button>
            </form>

            <?php if ($hasPendingSocial): ?>
                <div class="social-registration-exit">
                    <form method="POST" action="cancel-social-registration.php" onsubmit="return confirm('Cancel registration? Your entered information will be discarded, and no SmartBooking account will be created.');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $_SESSION['cancel_social_csrf'], ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="destination" value="register">
                        <button class="social-registration-cancel" type="submit">Cancel registration</button>
                    </form>
                    <form method="POST" action="cancel-social-registration.php">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $_SESSION['cancel_social_csrf'], ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="destination" value="login">
                        <p class="social-registration-signin">Already have an account? <button type="submit">Sign in here</button></p>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/display-preferences-widget.php'; ?>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<script src="../assets/js/customer-auth-v2.js"></script>
<script src="../assets/js/accessibility-preferences.js"></script>
</body>
</html>
