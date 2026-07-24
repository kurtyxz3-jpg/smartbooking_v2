<?php
declare(strict_types=1);

require_once __DIR__ . '/customer-auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/language.php';
require_once __DIR__ . '/customer-ui-helper.php';

$customerPage = $customerPage ?? '';
$pageTitle = $pageTitle ?? 'My Beauty Space';
$customerId = (int) $_SESSION['user_id'];
$customerFullName = trim((string) ($_SESSION['fullname'] ?? 'Customer'));
$customerFirstName = explode(' ', $customerFullName)[0] ?: 'Customer';
$customerFlash = customerFlash();
$customerProfilePicture = null;

try {
    $customerPhotoStatement = $pdo->prepare(
        "SELECT profile_picture
         FROM users
         WHERE id = ?
         LIMIT 1"
    );
    $customerPhotoStatement->execute([$customerId]);
    $customerProfilePicture = $customerPhotoStatement->fetchColumn() ?: null;
} catch (Throwable $exception) {
    $customerProfilePicture = null;
}

$customerProfilePictureUrl = $customerProfilePicture
    ? '../uploads/profile/'
        . rawurlencode(basename((string) $customerProfilePicture))
    : null;

?>
<!DOCTYPE html>
<html
    lang="<?= htmlspecialchars($languageCode, ENT_QUOTES, 'UTF-8') ?>"
    data-theme="<?= htmlspecialchars($themeMode, ENT_QUOTES, 'UTF-8') ?>"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | NBW</title>

    <script src="../assets/js/theme-bootstrap.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/customer-v2.css">
    <link rel="stylesheet" href="../assets/css/customer-interface.css">
    <link rel="stylesheet" href="../assets/css/customer-profile-redesign.css">
    <link rel="stylesheet" href="../assets/css/accessibility-preferences.css">
    <link rel="stylesheet" href="../assets/css/official-logo.css">
    <link rel="icon" href="../assets/images/branding/nbw-favicon.png">
</head>

<body class="customer-v2-body">
<header class="customer-topbar customer-app-header">
    <a class="customer-brand" href="dashboard.php">
        <img
            class="nbw-official-logo customer-brand-logo"
            src="../assets/images/branding/nbw-logo-transparent.webp"
            alt="Nails and Body Worx"
        >

        <div>
            <strong>Nails &amp; Body Worx</strong>
            <small>My Beauty Space</small>
        </div>
    </a>

    <button
        class="customer-menu-toggle"
        id="customerMenuToggle"
        type="button"
        aria-expanded="false"
        aria-controls="customerNavigation"
    >
        <span></span><span></span>
        <b>Menu</b>
    </button>

    <nav class="customer-nav" id="customerNavigation">
        <a class="<?= $customerPage === 'dashboard' ? 'active' : '' ?>" href="dashboard.php">Home</a>
        <a class="<?= $customerPage === 'services' ? 'active' : '' ?>" href="services.php">Services</a>
        <a class="<?= $customerPage === 'book' ? 'active' : '' ?>" href="book.php">Book</a>
        <a class="<?= in_array($customerPage, ['appointments', 'appointment-details'], true) ? 'active' : '' ?>" href="appointments.php">Appointments</a>
        <a class="<?= $customerPage === 'ai' ? 'active' : '' ?>" href="ai-generator.php">AI Studio</a>
        <a class="<?= $customerPage === 'payments' ? 'active' : '' ?>" href="payments.php">Payments</a>
        <a class="<?= $customerPage === 'feedback' ? 'active' : '' ?>" href="feedback.php">Feedback</a>
    </nav>

    <div class="customer-user-menu">
        <a class="customer-profile-link" href="profile.php">
            <span class="customer-header-profile-photo">
                <?php if ($customerProfilePictureUrl): ?>
                    <img
                        src="<?= htmlspecialchars(
                            $customerProfilePictureUrl,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        alt="<?= htmlspecialchars(
                            $customerFullName,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >
                <?php else: ?>
                    <b><?= htmlspecialchars(
                        strtoupper(substr($customerFirstName, 0, 1)),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?></b>
                <?php endif; ?>
            </span>
            <div>
                <strong><?= htmlspecialchars($customerFirstName, ENT_QUOTES, 'UTF-8') ?></strong>
                <small>My profile</small>
            </div>
        </a>

        <a class="customer-logout" href="../auth/logout.php" aria-label="Logout">↗</a>
    </div>
</header>

<?php if ($customerFlash): ?>
    <div class="customer-toast customer-toast-<?= htmlspecialchars($customerFlash['type'], ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($customerFlash['message'], ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>
