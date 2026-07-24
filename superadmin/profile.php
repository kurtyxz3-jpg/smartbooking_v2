<?php
declare(strict_types=1);
require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
$pageTitle='My Account';
$accountCenterRole='super_admin';
include '../includes/superadmin-header.php';
?>
<div class="dashboard"><?php include '../includes/superadmin-sidebar.php';?><main class="content"><?php include '../includes/account-center.php';?><?php include '../includes/superadmin-footer.php';?></main></div>
