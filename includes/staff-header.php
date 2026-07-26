<?php
require_once __DIR__ . '/language.php';
startInterfaceTranslation();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($languageCode, ENT_QUOTES, 'UTF-8') ?>"
      data-theme="<?= htmlspecialchars($themeMode, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle)
        ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' | NBW'
        : 'Staff | NBW' ?></title>

    <script src="../assets/js/theme-bootstrap.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/staff.css">
    <link rel="stylesheet" href="../assets/css/accessibility-preferences.css">
    <link rel="stylesheet" href="../assets/css/official-logo.css">
    <link rel="stylesheet" href="../assets/css/global-profile-display.css">
    <link rel="stylesheet" href="../assets/css/management-themes.css">
    <link rel="stylesheet" href="../assets/css/global-theme-system.css">
    <link rel="icon" type="image/png"
          href="../assets/images/branding/nbw-favicon.png">
    <script defer src="../assets/js/accessibility-preferences.js"></script>
</head>
<body class="management-portal staff-portal">
<?php include __DIR__ . '/display-preferences-widget.php'; ?>
