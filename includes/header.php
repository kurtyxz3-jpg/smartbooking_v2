<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | NBW' : 'Nails & Body Worx' ?></title>
<link rel="stylesheet" href="/smartbooking_v2/assets/css/style.css">
<link rel="stylesheet" href="/smartbooking_v2/assets/css/public-v3.css">
</head>
<body class="nbw-public">
<header class="nbw-header" id="siteHeader">
<a class="nbw-logo" href="/smartbooking_v2/index.php"><span>N</span><div><strong>Nails &amp; Body Worx</strong><small>Beauty Lounge</small></div></a>
<button class="nbw-menu-button" id="menuButton" type="button" aria-expanded="false"><i></i><i></i></button>
<nav class="nbw-nav" id="siteNav">
<a href="/smartbooking_v2/index.php">Home</a><a href="/smartbooking_v2/customer/services.php">Services</a>
<a href="/smartbooking_v2/index.php#gallery">Gallery</a><a href="/smartbooking_v2/index.php#packages">Packages</a>
<a href="/smartbooking_v2/index.php#visit">Visit Us</a><a href="/smartbooking_v2/auth/login.php">Sign in</a>
<a class="nbw-primary-button" href="/smartbooking_v2/customer/book.php">Book now ↗</a>
</nav></header>
