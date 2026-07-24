<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/admin-helper.php';
require_once '../includes/premium-admin-helper.php';

$pageTitle='Staff';
$flash=premiumAdminGetFlash();

if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!premiumAdminVerifyCsrf($_POST['csrf_token']??null))premiumAdminFlash('error','Session token expired.','staff.php');
 $id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
 $action=$_POST['action']??'';
 if($action==='toggle'&&$id){
  $pdo->prepare("UPDATE users SET status=CASE WHEN status='active' THEN 'inactive' ELSE 'active' END WHERE id=? AND role='staff'")->execute([$id]);
  premiumAdminFlash('success','Staff status updated.','staff.php');
 }
 if($action==='save'){
  $fullname=trim((string)($_POST['fullname']??''));$email=strtolower(trim((string)($_POST['email']??'')));$phone=trim((string)($_POST['phone_number']??''));$username=trim((string)($_POST['username']??''));$password=$_POST['password']??'';
  if($fullname===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||$username==='')premiumAdminFlash('error','Complete the required staff details.','staff.php');
  if($id){
   $sql="UPDATE users SET fullname=?,username=?,email=?,phone_number=?";$params=[$fullname,$username,$email,$phone?:null];
   if($password!==''){$sql.=",password=?";$params[]=password_hash($password,PASSWORD_DEFAULT);}
   $sql.=" WHERE id=? AND role='staff'";$params[]=$id;$pdo->prepare($sql)->execute($params);
  }else{
   if(strlen($password)<8)premiumAdminFlash('error','New staff password must have at least 8 characters.','staff.php');
   $pdo->prepare("INSERT INTO users(fullname,username,email,phone_number,password,role,status) VALUES(?,?,?,?,?,'staff','active')")->execute([$fullname,$username,$email,$phone?:null,password_hash($password,PASSWORD_DEFAULT)]);
  }
  premiumAdminFlash('success','Staff account saved.','staff.php');
 }
}

$search=trim((string)($_GET['search']??''));$status=trim((string)($_GET['status']??'all'));$where=["u.role='staff'"];$params=[];
if($search!==''){$where[]="(u.fullname LIKE :name_search OR u.email LIKE :email_search OR u.username LIKE :username_search)";$v='%'.$search.'%';$params=['name_search'=>$v,'email_search'=>$v,'username_search'=>$v];}
if($status!=='all'){$where[]='u.status=:status';$params['status']=$status;}
$stmt=$pdo->prepare("SELECT u.*,COUNT(b.id) AS assigned_count,SUM(b.status='completed') AS completed_count FROM users u LEFT JOIN bookings b ON b.staff_id=u.id WHERE ".implode(' AND ',$where)." GROUP BY u.id ORDER BY u.fullname");
premiumAdminBind($stmt,$params);$stmt->execute();$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>
<link rel="stylesheet" href="../assets/css/premium-admin-suite.css">
<div class="admin-layout"><?php include '../includes/admin-sidebar.php';?><main class="admin-content">
<header class="admin-page-header premium-admin-header"><div><p class="premium-admin-kicker">Team management</p><h1>Staff</h1><p>Manage staff accounts, activity, and access status.</p></div><button class="admin-btn admin-btn-primary" data-premium-modal-open="staffModal">＋ Add Staff</button></header>
<?php if($flash):?><div class="premium-admin-alert <?=htmlspecialchars($flash['type'])?>"><?=htmlspecialchars($flash['message'])?></div><?php endif;?>
<section class="admin-card premium-admin-toolbar"><div class="premium-admin-toolbar-head"><div><p class="premium-admin-kicker">Staff directory</p><h2>Find a team member</h2><p>Search by name, username, or email.</p></div></div><form method="GET" class="premium-admin-filter compact"><div class="admin-field"><label>Search</label><div class="premium-admin-search"><span>⌕</span><input name="search" value="<?=htmlspecialchars($search)?>" data-live-search="staffCards" placeholder="Name, email, or username"></div></div><div class="admin-field"><label>Status</label><select name="status"><option value="all">All statuses</option><option value="active" <?=$status==='active'?'selected':''?>>Active</option><option value="inactive" <?=$status==='inactive'?'selected':''?>>Inactive</option></select></div><div class="premium-admin-filter-actions"><button class="admin-btn admin-btn-primary">Apply</button><a class="admin-btn admin-btn-light" href="staff.php">Reset</a></div></form></section>
<div class="premium-admin-result-line"><strong><?=count($rows)?></strong><span>staff members</span></div>
<section class="premium-admin-cards" id="staffCards"><?php if(!$rows):?><div class="admin-card premium-admin-empty"><span>◇</span><h2>No staff accounts</h2><p>Add salon professionals from this page.</p></div><?php endif;?><?php foreach($rows as $row):?><article class="premium-admin-card" data-search-row="<?=htmlspecialchars(strtolower($row['fullname'].' '.$row['email'].' '.$row['username']))?>"><div class="premium-admin-card-body"><span class="premium-admin-badge <?=htmlspecialchars($row['status'])?>"><?=premiumAdminStatus($row['status'])?></span><h2><?=htmlspecialchars($row['fullname'])?></h2><p><?=htmlspecialchars($row['email'])?><br><?=htmlspecialchars($row['phone_number']??'No phone number')?></p><div class="premium-admin-card-meta"><div><small>Assigned bookings</small><strong><?=(int)$row['assigned_count']?></strong></div><div><small>Completed services</small><strong><?=(int)$row['completed_count']?></strong></div></div><div class="premium-admin-row-actions" style="margin-top:14px"><button class="primary" data-premium-modal-open="staffModal" data-payload="<?=htmlspecialchars(json_encode($row),ENT_QUOTES)?>">Edit</button><form method="POST"><input type="hidden" name="csrf_token" value="<?=premiumAdminCsrf()?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?=(int)$row['id']?>"><button><?=$row['status']==='active'?'Deactivate':'Activate'?></button></form></div></div></article><?php endforeach;?></section>
<?php include '../includes/admin-footer.php';?></main></div>
<div class="premium-admin-modal" id="staffModal"><div class="premium-admin-modal-backdrop" data-premium-modal-close></div><section class="premium-admin-modal-dialog"><header><div><p class="premium-admin-kicker">Staff account</p><h2>Add or Edit Staff</h2></div><button class="premium-admin-modal-close" data-premium-modal-close>×</button></header><form method="POST"><input type="hidden" name="csrf_token" value="<?=premiumAdminCsrf()?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" data-field="id"><div class="premium-admin-form-grid"><div class="admin-field premium-admin-span-2"><label>Full name</label><input name="fullname" data-field="fullname" required></div><div class="admin-field"><label>Username</label><input name="username" data-field="username" required></div><div class="admin-field"><label>Email</label><input name="email" type="email" data-field="email" required></div><div class="admin-field"><label>Phone number</label><input name="phone_number" data-field="phone_number"></div><div class="admin-field"><label>Password</label><input name="password" type="password" minlength="8"><small>Leave blank when editing to keep the current password.</small></div></div><div class="premium-admin-modal-actions"><button type="button" class="admin-btn admin-btn-light" data-premium-modal-close>Cancel</button><button class="admin-btn admin-btn-primary">Save Staff</button></div></form></section></div>
<script src="../assets/js/premium-admin-suite.js"></script>
