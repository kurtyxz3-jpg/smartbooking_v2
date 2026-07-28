<?php
declare(strict_types=1);

require_once __DIR__ . '/account-center-helper.php';

$userId = (int) $_SESSION['user_id'];
$role = (string) $_SESSION['role'];
$expectedRole = $accountCenterRole ?? $role;

if ($role !== $expectedRole) {
    header('Location: ../auth/login.php');
    exit;
}

$flash = accountCenterGetFlash();
$allowedTabs = [
    'overview',
    'profile',
    'security',
    'notifications',
    'connections',
    'access',
];

if ($role === 'customer') {
    $allowedTabs[] = 'activity';
}
$activeTab = trim((string) ($_GET['tab'] ?? 'overview'));

if (!in_array($activeTab, $allowedTabs, true)) {
    $activeTab = 'overview';
}

$userStatement = $pdo->prepare(
    "SELECT
        id,
        fullname,
        username,
        email,
        phone_number,
        role,
        status,
        profile_picture,
        created_at
     FROM users
     WHERE id = ?
     LIMIT 1"
);
$userStatement->execute([$userId]);
$user = $userStatement->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: ../auth/login.php');
    exit;
}

$preference = [
    'theme_mode' => 'default',
    'language_code' => 'english',
    'two_factor_enabled' => 0,
    'recovery_email' => '',
    'recovery_phone' => '',
];

if (accountCenterTableExists($pdo, 'account_preferences')) {
    $statement = $pdo->prepare(
        "SELECT *
         FROM account_preferences
         WHERE user_id = ?
         LIMIT 1"
    );
    $statement->execute([$userId]);
    $preference = array_merge(
        $preference,
        $statement->fetch(PDO::FETCH_ASSOC) ?: []
    );
}

$notifications = [];

if (accountCenterTableExists($pdo, 'account_notification_preferences')) {
    $statement = $pdo->prepare(
        "SELECT notification_key, enabled
         FROM account_notification_preferences
         WHERE user_id = ?"
    );
    $statement->execute([$userId]);

    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $notifications[$row['notification_key']] = (int) $row['enabled'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!accountCenterVerifyCsrf($_POST['csrf_token'] ?? null)) {
        accountCenterFlash('error', 'Your session token expired.', 'profile.php?tab=' . $activeTab);
    }

    $action = trim((string) ($_POST['action'] ?? ''));

    try {
        if ($action === 'profile') {
            $fullname = trim((string) ($_POST['fullname'] ?? ''));
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $phone = trim((string) ($_POST['phone_number'] ?? ''));

            if ($fullname === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Enter a valid name and email address.');
            }

            $duplicate = $pdo->prepare(
                "SELECT COUNT(*)
                 FROM users
                 WHERE email = ?
                   AND id <> ?"
            );
            $duplicate->execute([$email, $userId]);

            if ((int) $duplicate->fetchColumn() > 0) {
                throw new RuntimeException('That email address is already in use.');
            }

            $picture = accountCenterUploadProfileImage(
                $_FILES['profile_picture'] ?? [],
                $user['profile_picture'] ?? null
            );

            $statement = $pdo->prepare(
                "UPDATE users
                 SET fullname = ?,
                     email = ?,
                     phone_number = ?,
                     profile_picture = ?
                 WHERE id = ?"
            );
            $statement->execute([
                $fullname,
                $email,
                $phone !== '' ? $phone : null,
                $picture,
                $userId,
            ]);

            if ($role === 'customer' && accountCenterTableExists($pdo, 'customer_profiles')) {
                $parts = preg_split('/\s+/', $fullname) ?: [];
                $firstName = $parts[0] ?? 'Customer';
                $lastName = count($parts) > 1 ? (string) end($parts) : '';

                $statement = $pdo->prepare(
                    "INSERT INTO customer_profiles (
                        user_id,
                        first_name,
                        last_name
                    ) VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        first_name = VALUES(first_name),
                        last_name = VALUES(last_name)"
                );
                $statement->execute([$userId, $firstName, $lastName]);
            }

            $_SESSION['fullname'] = $fullname;
            $_SESSION['email'] = $email;

            accountCenterFlash('success', 'Your profile was updated.', 'profile.php?tab=profile');
        }

        if ($action === 'password') {
            $current = (string) ($_POST['current_password'] ?? '');
            $new = (string) ($_POST['new_password'] ?? '');
            $confirm = (string) ($_POST['confirm_password'] ?? '');

            $statement = $pdo->prepare(
                "SELECT password FROM users WHERE id = ?"
            );
            $statement->execute([$userId]);
            $hash = (string) $statement->fetchColumn();

            if (!password_verify($current, $hash)) {
                throw new RuntimeException('The current password is incorrect.');
            }

            if (strlen($new) < 8) {
                throw new RuntimeException('The new password must contain at least 8 characters.');
            }

            if ($new !== $confirm) {
                throw new RuntimeException('The password confirmation does not match.');
            }

            $statement = $pdo->prepare(
                "UPDATE users SET password = ? WHERE id = ?"
            );
            $statement->execute([
                password_hash($new, PASSWORD_DEFAULT),
                $userId,
            ]);

            if (accountCenterTableExists($pdo, 'account_preferences')) {
                $pdo->prepare(
                    "INSERT INTO account_preferences (
                        user_id,
                        password_changed_at
                    ) VALUES (?, NOW())
                    ON DUPLICATE KEY UPDATE
                        password_changed_at = NOW()"
                )->execute([$userId]);
            }

            accountCenterLog($pdo, $userId, 'password_changed');
            accountCenterFlash('success', 'Your password was changed.', 'profile.php?tab=security');
        }

        if ($action === 'security') {
            $recoveryEmail = strtolower(trim((string) ($_POST['recovery_email'] ?? '')));
            $recoveryPhone = trim((string) ($_POST['recovery_phone'] ?? ''));
            $twoFactor = isset($_POST['two_factor_enabled']) ? 1 : 0;

            if ($recoveryEmail !== '' && !filter_var($recoveryEmail, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Enter a valid recovery email.');
            }

            $statement = $pdo->prepare(
                "INSERT INTO account_preferences (
                    user_id,
                    two_factor_enabled,
                    recovery_email,
                    recovery_phone
                ) VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    two_factor_enabled = VALUES(two_factor_enabled),
                    recovery_email = VALUES(recovery_email),
                    recovery_phone = VALUES(recovery_phone)"
            );
            $statement->execute([
                $userId,
                $twoFactor,
                $recoveryEmail !== '' ? $recoveryEmail : null,
                $recoveryPhone !== '' ? $recoveryPhone : null,
            ]);

            accountCenterFlash(
                'success',
                'Security preferences were saved. Two-factor enforcement will be connected in the next security phase.',
                'profile.php?tab=security'
            );
        }

        if ($action === 'notifications') {
            $available = accountCenterNotifications($role);
            $pdo->beginTransaction();

            try {
                foreach ($available as $key => $label) {
                    $enabled = isset($_POST['notification'][$key]) ? 1 : 0;

                    $statement = $pdo->prepare(
                        "INSERT INTO account_notification_preferences (
                            user_id,
                            notification_key,
                            enabled
                        ) VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            enabled = VALUES(enabled)"
                    );
                    $statement->execute([$userId, $key, $enabled]);
                }

                $pdo->commit();
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                throw $exception;
            }

            accountCenterFlash('success', 'Notification preferences were saved.', 'profile.php?tab=notifications');
        }
    } catch (Throwable $exception) {
        accountCenterFlash('error', $exception->getMessage(), 'profile.php?tab=' . $activeTab);
    }
}

$loginHistory = [];

if (accountCenterTableExists($pdo, 'account_login_history')) {
    $statement = $pdo->prepare(
        "SELECT *
         FROM account_login_history
         WHERE user_id = ?
         ORDER BY created_at DESC
         LIMIT 12"
    );
    $statement->execute([$userId]);
    $loginHistory = $statement->fetchAll(PDO::FETCH_ASSOC);
}

$connections = [];

if (accountCenterTableExists($pdo, 'social_accounts')) {
    $statement = $pdo->prepare(
        "SELECT provider, provider_email, created_at
         FROM social_accounts
         WHERE user_id = ?
         ORDER BY provider"
    );
    $statement->execute([$userId]);
    $connections = $statement->fetchAll(PDO::FETCH_ASSOC);
}

$roleStats = [];

if ($role === 'admin') {
    $roleStats = [
        'Bookings available' => accountCenterTableExists($pdo, 'bookings')
            ? (int) $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn()
            : 0,
        'Active services' => accountCenterTableExists($pdo, 'services')
            ? (int) $pdo->query("SELECT COUNT(*) FROM services WHERE status='active'")->fetchColumn()
            : 0,
        'Active staff' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='staff' AND status='active'")->fetchColumn(),
    ];
} elseif ($role === 'staff') {
    $statement = $pdo->prepare(
        "SELECT
            COUNT(*) AS assigned_count,
            SUM(status='completed') AS completed_count
         FROM bookings
         WHERE staff_id = ?"
    );
    $statement->execute([$userId]);
    $stats = $statement->fetch(PDO::FETCH_ASSOC) ?: [];

    $roleStats = [
        'Assigned appointments' => (int) ($stats['assigned_count'] ?? 0),
        'Completed services' => (int) ($stats['completed_count'] ?? 0),
        'Account status' => ucfirst((string) $user['status']),
    ];
} elseif ($role === 'customer') {
    $statement = $pdo->prepare(
        "SELECT
            COUNT(*) AS booking_count,
            SUM(status='completed') AS completed_count
         FROM bookings
         WHERE customer_id = ?"
    );
    $statement->execute([$userId]);
    $stats = $statement->fetch(PDO::FETCH_ASSOC) ?: [];

    $roleStats = [
        'Total bookings' => (int) ($stats['booking_count'] ?? 0),
        'Completed visits' => (int) ($stats['completed_count'] ?? 0),
        'Connected accounts' => count($connections),
    ];
} else {
    $roleStats = [
        'Active administrators' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin' AND status='active'")->fetchColumn(),
        'Active users' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetchColumn(),
        'System role' => 'Full access',
    ];
}

$permissions = accountCenterPermissions($role);
$notificationOptions = accountCenterNotifications($role);
$profilePhoto = !empty($user['profile_picture'])
    ? '../uploads/profile/' . rawurlencode(basename((string) $user['profile_picture']))
    : null;
?>

<link rel="stylesheet" href="../assets/css/account-center.css">
<link rel="stylesheet" href="../assets/css/account-center-role-themes.css?v=096">

<section class="account-center-hero <?= $role === 'customer'
        ? 'account-center-customer-hero'
        : ''
    ?>">
    <div class="account-center-identity">
        <div class="account-center-avatar">
            <?php if ($profilePhoto): ?>
                <img src="<?= htmlspecialchars($profilePhoto, ENT_QUOTES, 'UTF-8') ?>" alt="">
            <?php else: ?>
                <span><?= htmlspecialchars(accountCenterInitials((string) $user['fullname']), ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div>
            <p class="account-center-kicker">My Account</p>
            <h1><?= htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p>
                <?= htmlspecialchars(accountCenterRoleName($role), ENT_QUOTES, 'UTF-8') ?>
                · @<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
            </p>

            <span class="account-center-status <?= htmlspecialchars($user['status'], ENT_QUOTES, 'UTF-8') ?>">
                <?= ucfirst((string) $user['status']) ?>
            </span>
        </div>
    </div>

</section>

<?php if ($flash): ?>
    <div class="account-center-alert <?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<nav class="account-center-tabs">
    <?php
    $accountCenterTabs = [
        'overview' => 'Overview',
        'profile' => 'Profile',
        'security' => 'Security',
        'notifications' => 'Notifications',
        'connections' => 'Connected Accounts',
        'access' => 'Access',
    ];

    if ($role === 'customer') {
        $accountCenterTabs['activity'] = 'Activity';
    }
    ?>
    <?php foreach ($accountCenterTabs as $tab => $label): ?>
        <a class="<?= $activeTab === $tab ? 'active' : '' ?>" href="?tab=<?= $tab ?>">
            <?= $label ?>
        </a>
    <?php endforeach; ?>
</nav>

<div class="account-center-content">
<?php if ($activeTab === 'overview'): ?>
    <div class="account-center-stat-grid">
        <?php foreach ($roleStats as $label => $value): ?>
            <article>
                <small><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></small>
                <strong><?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?></strong>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="account-center-grid">
        <section class="account-center-card span-7">
            <div class="account-center-card-heading">
                <div><p class="account-center-kicker">Account summary</p><h2>Personal information</h2></div>
                <a href="?tab=profile">Edit profile →</a>
            </div>

            <dl class="account-center-details">
                <div><dt>Full name</dt><dd><?= htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8') ?></dd></div>
                <div><dt>Username</dt><dd>@<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></dd></div>
                <div><dt>Email</dt><dd><?= htmlspecialchars($user['email'] ?: 'Not provided', ENT_QUOTES, 'UTF-8') ?></dd></div>
                <div><dt>Phone</dt><dd><?= htmlspecialchars($user['phone_number'] ?: 'Not provided', ENT_QUOTES, 'UTF-8') ?></dd></div>
            </dl>
        </section>

        <aside class="account-center-card span-5">
            <div class="account-center-card-heading">
                <div><p class="account-center-kicker">Security health</p><h2>Account protection</h2></div>
            </div>

            <div class="account-center-security-summary">
                <div><span>Password</span><strong><?= !empty($preference['password_changed_at']) ? 'Updated ' . date('M j, Y', strtotime($preference['password_changed_at'])) : 'Active' ?></strong></div>
                <div><span>Recovery email</span><strong><?= htmlspecialchars($preference['recovery_email'] ?: 'Not configured', ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div><span>2FA preparation</span><strong><?= (int) $preference['two_factor_enabled'] === 1 ? 'Requested' : 'Not requested' ?></strong></div>
            </div>

            <a class="account-center-button" href="?tab=security">Review security</a>
        </aside>
    </div>

<?php elseif ($activeTab === 'profile'): ?>
    <section class="account-center-card">
        <div class="account-center-card-heading">
            <div><p class="account-center-kicker">Personal information</p><h2>Edit your profile</h2><p>Your username and role can only be changed by authorized system management.</p></div>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= accountCenterCsrf() ?>">
            <input type="hidden" name="action" value="profile">

            <div class="account-center-profile-editor">
                <div class="account-center-photo-editor">
                    <div class="account-center-avatar large">
                        <?php if ($profilePhoto): ?>
                            <img id="accountPhotoPreview" src="<?= htmlspecialchars($profilePhoto, ENT_QUOTES, 'UTF-8') ?>" alt="">
                        <?php else: ?>
                            <span id="accountPhotoInitials"><?= htmlspecialchars(accountCenterInitials((string) $user['fullname']), ENT_QUOTES, 'UTF-8') ?></span>
                            <img id="accountPhotoPreview" src="" alt="" hidden>
                        <?php endif; ?>
                    </div>

                    <label class="account-center-upload">
                        Change photo
                        <input id="accountPhotoInput" name="profile_picture" type="file" accept=".jpg,.jpeg,.png,.webp">
                    </label>
                    <small>JPG, PNG, or WebP. Maximum 4 MB.</small>
                </div>

                <div class="account-center-form-grid">
                    <div class="admin-field"><label>Full name</label><input name="fullname" value="<?= htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8') ?>" required></div>
                    <div class="admin-field"><label>Username</label><input value="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>" disabled></div>
                    <div class="admin-field"><label>Email</label><input name="email" type="email" value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></div>
                    <div class="admin-field"><label>Phone number</label><input name="phone_number" value="<?= htmlspecialchars($user['phone_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                    <div class="admin-field"><label>Role</label><input value="<?= htmlspecialchars(accountCenterRoleName($role), ENT_QUOTES, 'UTF-8') ?>" disabled></div>
                    <div class="admin-field"><label>Account status</label><input value="<?= ucfirst((string) $user['status']) ?>" disabled></div>
                </div>
            </div>

            <div class="account-center-form-actions">
                <button class="account-center-button primary">Save profile</button>
            </div>
        </form>
    </section>

<?php elseif ($activeTab === 'security'): ?>
    <div class="account-center-grid">
        <section class="account-center-card span-7">
            <div class="account-center-card-heading">
                <div><p class="account-center-kicker">Password</p><h2>Change your password</h2><p>Use at least eight characters and avoid reusing a password from another account.</p></div>
            </div>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= accountCenterCsrf() ?>">
                <input type="hidden" name="action" value="password">

                <div class="account-center-form-grid">
                    <div class="admin-field full"><label>Current password</label><input name="current_password" type="password" required></div>
                    <div class="admin-field"><label>New password</label><input name="new_password" type="password" minlength="8" required></div>
                    <div class="admin-field"><label>Confirm password</label><input name="confirm_password" type="password" minlength="8" required></div>
                </div>

                <div class="account-center-form-actions">
                    <button class="account-center-button primary">Update password</button>
                </div>
            </form>
        </section>

        <aside class="account-center-card span-5">
            <div class="account-center-card-heading">
                <div><p class="account-center-kicker">Recovery</p><h2>Security preferences</h2><p>Save recovery information. Two-factor authentication enforcement is a future security connection.</p></div>
            </div>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= accountCenterCsrf() ?>">
                <input type="hidden" name="action" value="security">

                <div class="account-center-form-grid">
                    <div class="admin-field full"><label>Recovery email</label><input name="recovery_email" type="email" value="<?= htmlspecialchars($preference['recovery_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                    <div class="admin-field full"><label>Recovery phone</label><input name="recovery_phone" value="<?= htmlspecialchars($preference['recovery_phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                    <label class="account-center-toggle full">
                        <input name="two_factor_enabled" type="checkbox" <?= (int) $preference['two_factor_enabled'] === 1 ? 'checked' : '' ?>>
                        <span><strong>Prepare this account for two-factor authentication</strong><small>This stores the preference only; OTP enforcement will be connected in the security phase.</small></span>
                    </label>
                </div>

                <div class="account-center-form-actions">
                    <button class="account-center-button">Save security preferences</button>
                </div>
            </form>
        </aside>
    </div>

<?php elseif ($activeTab === 'notifications'): ?>
    <section class="account-center-card">
        <div class="account-center-card-heading">
            <div><p class="account-center-kicker">Alert preferences</p><h2>Notifications for your role</h2><p>Choose the operational or customer updates that should be associated with your account.</p></div>
        </div>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= accountCenterCsrf() ?>">
            <input type="hidden" name="action" value="notifications">

            <div class="account-center-preference-list">
                <?php foreach ($notificationOptions as $key => $label): ?>
                    <label class="account-center-toggle">
                        <input
                            name="notification[<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>]"
                            type="checkbox"
                            <?= ($notifications[$key] ?? 1) === 1 ? 'checked' : '' ?>
                        >
                        <span>
                            <strong><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></strong>
                            <small>Receive this alert when the notification delivery module is active.</small>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="account-center-form-actions">
                <button class="account-center-button primary">Save notification preferences</button>
            </div>
        </form>
    </section>

<?php elseif ($activeTab === 'activity'): ?>
    <section class="account-center-card">
        <div class="account-center-card-heading">
            <div><p class="account-center-kicker">Account activity</p><h2>Recent security events</h2><p>Successful logins and password changes recorded after this update is installed.</p></div>
        </div>

        <?php if ($loginHistory): ?>
            <div class="account-center-activity-list">
                <?php foreach ($loginHistory as $entry): ?>
                    <article>
                        <span class="account-center-activity-icon">↗</span>
                        <div>
                            <strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', $entry['event_type'])), ENT_QUOTES, 'UTF-8') ?></strong>
                            <small><?= htmlspecialchars($entry['ip_address'] ?: 'Unknown IP', ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars(substr($entry['user_agent'] ?: 'Unknown browser', 0, 90), ENT_QUOTES, 'UTF-8') ?></small>
                        </div>
                        <time><?= date('M j, Y g:i A', strtotime($entry['created_at'])) ?></time>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="account-center-empty">
                <span>◷</span>
                <h2>No recorded account activity yet.</h2>
                <p>New login events will appear after the updated login file is installed.</p>
            </div>
        <?php endif; ?>
    </section>

<?php elseif ($activeTab === 'connections'): ?>
    <section class="account-center-card">
        <div class="account-center-card-heading">
            <div><p class="account-center-kicker">Connected identities</p><h2>Google and Facebook</h2><p>Social accounts appear after a successful OAuth login or account link.</p></div>
        </div>

        <div class="account-center-connections">
            <?php foreach (['google' => 'Google', 'facebook' => 'Facebook'] as $provider => $label): ?>
                <?php
                $connected = null;
                foreach ($connections as $connection) {
                    if ($connection['provider'] === $provider) {
                        $connected = $connection;
                        break;
                    }
                }
                ?>
                <article>
                    <span class="provider <?= $provider ?>"><?= $provider === 'google' ? 'G' : 'f' ?></span>
                    <div><strong><?= $label ?></strong><small><?= $connected ? htmlspecialchars($connected['provider_email'] ?: 'Connected account', ENT_QUOTES, 'UTF-8') : 'Not connected' ?></small></div>
                    <span class="connection-status <?= $connected ? 'connected' : '' ?>"><?= $connected ? 'Connected' : 'Available after OAuth setup' ?></span>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

<?php elseif ($activeTab === 'access'): ?>
    <section class="account-center-card">
        <div class="account-center-card-heading">
            <div><p class="account-center-kicker">Role permissions</p><h2><?= htmlspecialchars(accountCenterRoleName($role), ENT_QUOTES, 'UTF-8') ?> access</h2><p>This is a read-only summary. Role permissions are controlled by the system.</p></div>
        </div>

        <div class="account-center-permission-list">
            <?php foreach ($permissions as [$label, $allowed]): ?>
                <div class="<?= $allowed ? 'allowed' : 'restricted' ?>">
                    <span><?= $allowed ? '✓' : '×' ?></span>
                    <strong><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></strong>
                    <small><?= $allowed ? 'Allowed' : 'Restricted' ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
</div>

<script src="../assets/js/account-center.js"></script>
