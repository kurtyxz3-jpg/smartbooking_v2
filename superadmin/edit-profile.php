<?php require_once '../includes/superadmin-auth.php'; require_once '../config/database.php'; $pageTitle='Edit Profile'; include '../includes/superadmin-header.php'; ?>
<div class="dashboard"><?php include '../includes/superadmin-sidebar.php';?><main class="content"><div class="page-header"><h1 class="gold-title">Edit Profile</h1></div>
<section class="card"><p>This page will update the Super Administrator information.</p>
<form><div class="profile-fields"><div><label>Full Name</label><input></div><div><label>Email</label><input></div><div><label>Phone</label><input></div></div><button class="btn btn-gold" type="button">Save Changes</button></form>
</section><?php include '../includes/superadmin-footer.php';?></main></div>