<?php
declare(strict_types=1);
require_once '../includes/customer-auth.php';
require_once '../config/database.php';
require_once '../includes/language.php';
$pageTitle='My Account';
$accountCenterRole='customer';
include '../includes/customer-header.php';
?>
<main class="customer-page-shell account-center-customer-shell"><?php include '../includes/account-center.php';?></main>
<?php include '../includes/customer-footer.php';?>
