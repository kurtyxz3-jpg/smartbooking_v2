<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= isset($pageTitle)
            ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' | NBW'
            : 'Super Admin | NBW' ?>
    </title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/superadmin-settings.css">
</head>
<body>
