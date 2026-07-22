<?php
require_once __DIR__ . '/language.php';
startInterfaceTranslation();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($languageCode, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= isset($pageTitle)
            ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' | NBW'
            : 'Super Admin | NBW' ?>
    </title>

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
</head>
<body>
