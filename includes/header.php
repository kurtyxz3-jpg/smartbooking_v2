<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' | NBW' : 'NBW' ?></title>
<link rel="stylesheet" href="/smartbooking_v2/assets/css/style.css">
</head>
<body>
<header class="site-header">
  <a class="site-brand" href="/smartbooking_v2/index.php">
    <span class="site-brand-mark">N</span>
    <span class="site-brand-copy">                         
      <strong>NBW</strong>
      <small>Nails &amp; Body Worx</small>
    </span>
  </a>
  <nav class="site-nav">
    <a href="/smartbooking_v2/index.php">Home</a>
    <a href="/smartbooking_v2/customer/services.php">Services</a>
    <a href="/smartbooking_v2/customer/book.php">Book</a>
    <a class="btn btn-gold" href="/smartbooking_v2/auth/login.php">Sign In</a>
  </nav>
</header>
