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
        : 'Super Admin | NBW' ?></title>

    <script src="../assets/js/theme-bootstrap.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/superadmin.css">
    <link rel="stylesheet" href="../assets/css/superadmin-settings.css">
    <link rel="stylesheet" href="../assets/css/admin-management.css">
    <link rel="stylesheet" href="../assets/css/audit-logs.css">
    <link rel="stylesheet" href="../assets/css/superadmin-profile.css">
    <link rel="stylesheet" href="../assets/css/staff-management.css">
    <link rel="stylesheet" href="../assets/css/employment-records.css">
    <link rel="stylesheet" href="../assets/css/superadmin-analytics.css">
    <link rel="stylesheet" href="../assets/css/language-settings.css">
    <link rel="stylesheet" href="../assets/css/accessibility-preferences.css">
    <link rel="stylesheet" href="../assets/css/official-logo.css">
    <link rel="stylesheet" href="../assets/css/global-profile-display.css">
    <link rel="stylesheet" href="../assets/css/management-themes.css?v=084">
    <link rel="stylesheet" href="../assets/css/global-theme-system.css?v=083">
    <link rel="stylesheet" href="../assets/css/superadmin-button-contract.css?v=097">
    <link rel="icon" type="image/png"
          href="../assets/images/branding/nbw-favicon.png">
    <script defer src="../assets/js/accessibility-preferences.js"></script>
</head>
<body class="management-portal superadmin-portal">
<?php include __DIR__ . '/display-preferences-widget.php'; ?>
