<?php
declare(strict_types=1);
require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
$pageTitle='Profile';

$userId=$_SESSION['user_id'];
$stmt=$pdo->prepare("SELECT fullname,username,email,phone_number,profile_picture,created_at FROM users WHERE id=?");
$stmt->execute([$userId]);
$user=$stmt->fetch(PDO::FETCH_ASSOC) ?: [];

include '../includes/superadmin-header.php';
?>
<div class="dashboard">
<?php include '../includes/superadmin-sidebar.php'; ?>
<main class="content">
<div class="page-header"><div><h1 class="gold-title">Super Administrator Profile</h1><p>Manage your account and security.</p></div></div>

<div class="profile-grid">
<section class="card profile-card">
<div class="profile-top">
<div class="avatar">
<?php if(!empty($user['profile_picture'])): ?>
<img src="../uploads/profile/<?= htmlspecialchars($user['profile_picture']) ?>" alt="">
<?php else: ?><div class="avatar-placeholder">SA</div><?php endif;?>
</div>
<div>
<h2><?= htmlspecialchars($user['fullname']??'') ?></h2>
<p>@<?= htmlspecialchars($user['username']??'') ?></p>
<span class="role-badge">Super Administrator</span>
</div>
</div>

<div class="profile-fields">
<div><label>Email</label><input readonly value="<?= htmlspecialchars($user['email']??'') ?>"></div>
<div><label>Phone</label><input readonly value="<?= htmlspecialchars($user['phone_number']??'') ?>"></div>
<div><label>Account Created</label><input readonly value="<?= htmlspecialchars($user['created_at']??'') ?>"></div>
</div>

<div class="button-row">
<a class="btn btn-gold" href="edit-profile.php">Edit Profile</a>
<a class="btn btn-light" href="change-password.php">Change Password</a>
</div>
</section>

<section class="card">
<h2>Account Information</h2>
<table class="profile-info">
<tr><th>Role</th><td>Super Administrator</td></tr>
<tr><th>Current Session</th><td>Active</td></tr>
<tr><th>IP Address</th><td><?= htmlspecialchars($_SERVER['REMOTE_ADDR']??'Unknown') ?></td></tr>
<tr><th>Browser</th><td><?= htmlspecialchars($_SERVER['HTTP_USER_AGENT']??'Unknown') ?></td></tr>
</table>
</section>
</div>

<?php include '../includes/superadmin-footer.php'; ?>
</main></div>