<?php
declare(strict_types=1);
require_once '../includes/staff-auth.php';
require_once '../config/database.php';
require_once '../includes/staff-helper.php';
$pageTitle='My Account';
$accountCenterRole='staff';
include '../includes/staff-header.php';
?>
<div class="staff-layout"><?php include '../includes/staff-sidebar.php';?><main class="staff-content"><?php include '../includes/account-center.php';?><?php include '../includes/staff-footer.php';?></main></div>
