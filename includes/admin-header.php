<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= isset($pageTitle)
            ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' | NBW'
            : 'Admin | NBW' ?>
    </title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <link rel="icon" type="image/png" href="../assets/images/branding/nbw-favicon.png">
    <link rel="stylesheet" href="../assets/css/official-logo.css">
    <link rel="stylesheet" href="../assets/css/global-profile-display.css">
</head>
<body>
