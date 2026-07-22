<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta
        name="description"
        content="Nails & Body Worx SmartBooking — salon services, AI nail styling, and online appointments."
    >

    <title>
        <?= isset($pageTitle)
            ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' | NBW'
            : 'Nails & Body Worx' ?>
    </title>

    <link rel="stylesheet" href="/smartbooking_v2/assets/css/style.css">
    <link rel="stylesheet" href="/smartbooking_v2/assets/css/public-v2.css">
</head>

<body class="public-v2-body">
<header class="v2-site-header" id="siteHeader">
    <a class="v2-brand" href="/smartbooking_v2/index.php" aria-label="Nails and Body Worx home">
        <span class="v2-brand-symbol">N</span>

        <span>
            <strong>Nails &amp; Body Worx</strong>
            <small>SmartBooking</small>
        </span>
    </a>

    <button
        class="v2-menu-toggle"
        id="menuToggle"
        type="button"
        aria-label="Open navigation"
        aria-expanded="false"
    >
        <span></span>
        <span></span>
    </button>

    <nav class="v2-site-nav" id="siteNav">
        <a href="/smartbooking_v2/index.php#home">Home</a>
        <a href="/smartbooking_v2/index.php#services">Services</a>
        <a href="/smartbooking_v2/index.php#experience">Experience</a>
        <a href="/smartbooking_v2/index.php#faq">FAQ</a>

        <a class="v2-nav-login" href="/smartbooking_v2/auth/login.php">
            Sign in
        </a>

        <a class="v2-btn v2-btn-primary v2-nav-book" href="/smartbooking_v2/customer/book.php">
            Book now
        </a>
    </nav>
</header>
