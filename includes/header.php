<?php
declare(strict_types=1);

require_once __DIR__ . '/language.php';
startInterfaceTranslation();
?>
<!DOCTYPE html>
<html
    lang="<?= htmlspecialchars($languageCode, ENT_QUOTES, 'UTF-8') ?>"
    data-theme="<?= htmlspecialchars($themeMode, ENT_QUOTES, 'UTF-8') ?>"
>
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | NBW' : 'Nails & Body Worx' ?></title>
<script src="/smartbooking_v2/assets/js/theme-bootstrap.js"></script>
<link rel="stylesheet" href="/smartbooking_v2/assets/css/style.css">
<link rel="stylesheet" href="/smartbooking_v2/assets/css/public-v3.css">
<link rel="stylesheet" href="/smartbooking_v2/assets/css/accessibility-preferences.css">
    <link rel="icon" type="image/png" href="/smartbooking_v2/assets/images/branding/nbw-favicon.png">
    <link rel="stylesheet" href="/smartbooking_v2/assets/css/official-logo.css">
</head>
<body class="nbw-public">
<header class="nbw-header" id="siteHeader">
<a class="nbw-logo" href="/smartbooking_v2/index.php"><img
    class="nbw-official-logo nbw-logo-image"
    src="/smartbooking_v2/assets/images/branding/nbw-official-logo.webp"
    alt="Nails and Body Worx official logo"
><div class="nbw-brand-wordmark"><strong>Nails &amp; Body Worx</strong><small>Beauty Lounge</small></div></a>
<button class="nbw-menu-button" id="menuButton" type="button" aria-expanded="false"><i></i><i></i></button>
<nav class="nbw-nav" id="siteNav">
<a href="/smartbooking_v2/index.php">Home</a><a href="/smartbooking_v2/customer/services.php">Services</a>
<a href="/smartbooking_v2/index.php#gallery">Gallery</a><a href="/smartbooking_v2/index.php#packages">Packages</a>
<a href="/smartbooking_v2/index.php#visit">Visit Us</a><?php if (!empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'customer'): ?>
<a href="/smartbooking_v2/customer/dashboard.php">My account</a>
<?php else: ?>
<a href="/smartbooking_v2/auth/login.php">Sign in</a>
<?php endif; ?>
<a class="nbw-primary-button" href="/smartbooking_v2/customer/book.php">Book now ↗</a>
</nav></header>
