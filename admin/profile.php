<?php
declare(strict_types=1);
require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/admin-helper.php';
$pageTitle='My Account';
$accountCenterRole='admin';
include '../includes/admin-header.php';
?>
<div class="admin-layout"><?php include '../includes/admin-sidebar.php';?><main class="admin-content"><?php include '../includes/account-center.php';?><?php include '../includes/admin-footer.php';?></main></div>
