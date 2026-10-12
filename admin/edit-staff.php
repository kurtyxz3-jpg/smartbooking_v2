<?php
declare(strict_types=1);
require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/branch-context.php';
require_once '../includes/premium-admin-helper.php';

$pageTitle='Edit Staff';
$branchId=requireAssignedBranch($pdo);
$staffId=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$error='';
if(!$staffId){header('Location: staff.php');exit;}

$load=$pdo->prepare("SELECT u.id,u.fullname,u.username,u.email,u.phone_number,u.status,sp.specialty,sp.employment_status FROM users u LEFT JOIN staff_profiles sp ON sp.user_id=u.id WHERE u.id=? AND u.role='staff' AND u.branch_id=? LIMIT 1");
$load->execute([$staffId,$branchId]);
$staff=$load->fetch(PDO::FETCH_ASSOC);
if(!$staff){header('Location: staff.php');exit;}

$fullname=(string)$staff['fullname'];$username=(string)$staff['username'];$email=(string)$staff['email'];
$phone=(string)($staff['phone_number']??'');$status=(string)$staff['status'];$specialty=(string)($staff['specialty']??'');
$employment=(string)($staff['employment_status']??'full_time');

if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!premiumAdminVerifyCsrf($_POST['csrf_token']??null)){$error='Your session expired. Refresh and try again.';}
 else{
  $fullname=trim((string)($_POST['fullname']??''));$username=trim((string)($_POST['username']??''));
  $email=strtolower(trim((string)($_POST['email']??'')));$phone=trim((string)($_POST['phone_number']??''));
  $status=(string)($_POST['status']??'active');$specialty=trim((string)($_POST['specialty']??''));
  $employment=(string)($_POST['employment_status']??'full_time');$password=(string)($_POST['new_password']??'');
  $confirm=(string)($_POST['confirm_password']??'');
  if($fullname===''||$username===''||!filter_var($email,FILTER_VALIDATE_EMAIL))$error='Complete all required account information.';
  elseif(!in_array($status,['active','inactive'],true)||!in_array($employment,['full_time','part_time','contractual'],true))$error='Choose valid account and employment statuses.';
  elseif($password!==''&&strlen($password)<8)$error='A new password must contain at least 8 characters.';
  elseif($password!==$confirm)$error='The password confirmation does not match.';
  else{
   $duplicate=$pdo->prepare("SELECT id FROM users WHERE (username=? OR email=?) AND id<>? LIMIT 1");$duplicate->execute([$username,$email,$staffId]);
   if($duplicate->fetch())$error='The username or email is already used by another account.';
   else try{
    $pdo->beginTransaction();
    $sql="UPDATE users SET fullname=?,username=?,email=?,phone_number=?,status=?";$params=[$fullname,$username,$email,$phone?:null,$status];
    if($password!==''){$sql.=',password=?';$params[]=password_hash($password,PASSWORD_DEFAULT);}
    $sql.=" WHERE id=? AND role='staff' AND branch_id=?";$params[]=$staffId;$params[]=$branchId;$pdo->prepare($sql)->execute($params);
    $pdo->prepare("INSERT INTO staff_profiles(user_id,specialty,branch,employment_status) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE specialty=VALUES(specialty),branch=VALUES(branch),employment_status=VALUES(employment_status)")->execute([$staffId,$specialty?:null,branchName($pdo,$branchId),$employment]);
    $pdo->commit();premiumAdminFlash('success','Staff information updated.','staff.php');
   }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$error='The staff information could not be updated.';}
  }
 }
}
include '../includes/admin-header.php';
?>
<link rel="stylesheet" href="../assets/css/premium-admin-suite.css">
<div class="admin-layout"><?php include '../includes/admin-sidebar.php';?><main class="admin-content">
<header class="admin-page-header premium-admin-header"><div><p class="premium-admin-kicker">Team management</p><h1>Edit Staff</h1><p>Update account access and employment details for the salon.</p></div><a class="admin-btn admin-btn-light" href="staff.php">Back to Staff</a></header>
<?php if($error!==''):?><div class="premium-admin-alert error"><?=htmlspecialchars($error)?></div><?php endif;?>
<section class="admin-card" style="padding:24px"><form method="POST"><input type="hidden" name="csrf_token" value="<?=premiumAdminCsrf()?>">
<div class="premium-admin-form-grid"><div class="admin-field premium-admin-span-2"><label>Full name</label><input name="fullname" value="<?=htmlspecialchars($fullname)?>" required></div><div class="admin-field"><label>Username</label><input name="username" value="<?=htmlspecialchars($username)?>" required></div><div class="admin-field"><label>Email address</label><input name="email" type="email" value="<?=htmlspecialchars($email)?>" required></div><div class="admin-field"><label>Phone number</label><input name="phone_number" value="<?=htmlspecialchars($phone)?>"></div><div class="admin-field"><label>Specialty</label><input name="specialty" value="<?=htmlspecialchars($specialty)?>" placeholder="e.g. Nail technician"></div><div class="admin-field"><label>Employment status</label><select name="employment_status"><?php foreach(['full_time'=>'Full Time','part_time'=>'Part Time','contractual'=>'Contractual'] as $v=>$label):?><option value="<?=$v?>" <?=$employment===$v?'selected':''?>><?=$label?></option><?php endforeach;?></select></div><div class="admin-field"><label>Account status</label><select name="status"><option value="active" <?=$status==='active'?'selected':''?>>Active</option><option value="inactive" <?=$status==='inactive'?'selected':''?>>Inactive</option></select></div><div class="admin-field"><label>New password</label><input name="new_password" type="password" minlength="8" placeholder="Leave blank to keep current password"></div><div class="admin-field"><label>Confirm new password</label><input name="confirm_password" type="password"></div></div>
<div class="premium-admin-row-actions" style="margin-top:22px"><button class="admin-btn admin-btn-primary">Update Staff</button><a class="admin-btn admin-btn-light" href="staff.php">Cancel</a></div></form></section>
<?php include '../includes/admin-footer.php';?></main></div>
