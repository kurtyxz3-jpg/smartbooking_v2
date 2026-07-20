<?php require_once '../includes/superadmin-auth.php'; $pageTitle='Change Password'; include '../includes/superadmin-header.php'; ?>
<div class="dashboard"><?php include '../includes/superadmin-sidebar.php';?><main class="content"><div class="page-header"><h1 class="gold-title">Change Password</h1></div>
<section class="card"><form class="profile-fields">
<div><label>Current Password</label><input type="password"></div>
<div><label>New Password</label><input type="password"></div>
<div><label>Confirm Password</label><input type="password"></div>
</form><button class="btn btn-gold" type="button">Update Password</button></section>
<?php include '../includes/superadmin-footer.php';?></main></div>