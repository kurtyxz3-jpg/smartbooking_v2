<?php
declare(strict_types=1);
require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
$pageTitle='Staff Accounts';
$message='';
$messageType='success';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $action=$_POST['action']??'';
    $staffId=filter_input(INPUT_POST,'staff_id',FILTER_VALIDATE_INT);

    if($staffId && $action==='toggle_status'){
        $stmt=$pdo->prepare("UPDATE users SET status=CASE WHEN status='active' THEN 'inactive' ELSE 'active' END WHERE id=? AND role='staff'");
        $stmt->execute([$staffId]);
        $message='Staff account status updated successfully.';
    }

    if($staffId && $action==='delete'){
        $pdo->beginTransaction();
        try{
            $pdo->prepare("DELETE FROM staff_profiles WHERE user_id=?")->execute([$staffId]);
            $pdo->prepare("DELETE FROM users WHERE id=? AND role='staff'")->execute([$staffId]);
            $pdo->commit();
            $message='Staff account deleted successfully.';
            $messageType='danger';
        }catch(Throwable $e){
            if($pdo->inTransaction()){$pdo->rollBack();}
            $message='Unable to delete the staff account.';
            $messageType='danger';
        }
    }
}

$search=trim($_GET['search']??'');
$status=$_GET['status']??'';
$specialty=trim($_GET['specialty']??'');

$sql="SELECT u.id,u.fullname,u.username,u.email,u.phone_number,u.status,u.created_at,
             sp.specialty,sp.branch,sp.employment_status
      FROM users u
      LEFT JOIN staff_profiles sp ON sp.user_id=u.id
      WHERE u.role='staff'";
$params=[];

if($search!==''){
    $sql.=" AND (u.fullname LIKE :n OR u.username LIKE :u OR u.email LIKE :e OR u.phone_number LIKE :p)";
    $term='%'.$search.'%';
    $params=['n'=>$term,'u'=>$term,'e'=>$term,'p'=>$term];
}
if(in_array($status,['active','inactive'],true)){
    $sql.=" AND u.status=:status";
    $params['status']=$status;
}
if($specialty!==''){
    $sql.=" AND sp.specialty=:specialty";
    $params['specialty']=$specialty;
}
$sql.=" ORDER BY u.id DESC";

$stmt=$pdo->prepare($sql);
$stmt->execute($params);
$staffMembers=$stmt->fetchAll();

$specialties=$pdo->query("SELECT DISTINCT specialty FROM staff_profiles WHERE specialty IS NOT NULL AND specialty<>'' ORDER BY specialty")->fetchAll(PDO::FETCH_COLUMN);

include '../includes/superadmin-header.php';
?>
<div class="dashboard">
<?php include '../includes/superadmin-sidebar.php'; ?>
<main class="content">

<div class="page-header staff-page-header">
<div><h1 class="gold-title">Staff Accounts</h1><p>Create and manage staff access for Nails &amp; Body Worx.</p></div>
<div class="staff-header-actions"><a class="btn btn-light" href="staff-roles.php">Roles</a><a class="btn btn-gold" href="add-staff.php">Add Staff</a></div>
</div>

<?php if($message!==''): ?>
<div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<section class="card staff-filter-card">
<div class="staff-section-title"><h2>Search &amp; Filter</h2><p>Find staff by name, contact information, status, or specialty.</p></div>
<form class="staff-filter-form" method="GET">
<div class="filter-field"><label for="search">Search</label><input id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Name, username, email, or phone"></div>
<div class="filter-field"><label for="status">Account Status</label><select id="status" name="status"><option value="">All statuses</option><option value="active" <?= $status==='active'?'selected':'' ?>>Active</option><option value="inactive" <?= $status==='inactive'?'selected':'' ?>>Inactive</option></select></div>
<div class="filter-field"><label for="specialty">Specialty</label><select id="specialty" name="specialty"><option value="">All specialties</option><?php foreach($specialties as $option): ?><option value="<?= htmlspecialchars($option) ?>" <?= $specialty===$option?'selected':'' ?>><?= htmlspecialchars($option) ?></option><?php endforeach; ?></select></div>
<div class="staff-filter-actions"><button class="btn btn-gold" type="submit">Filter</button><a class="btn btn-light" href="staff.php">Reset</a></div>
</form>
</section>

<section class="card staff-table-card">
<div class="staff-table-heading"><h2>Registered Staff</h2><p><?= count($staffMembers) ?> staff record(s) found.</p></div>
<div class="table-wrap">
<table class="staff-table">
<thead><tr><th>Staff Member</th><th>Contact</th><th>Specialty</th><th>Branch</th><th>Employment</th><th>Account</th><th>Actions</th></tr></thead>
<tbody>
<?php if(!$staffMembers): ?>
<tr><td colspan="7" class="empty-state">No staff accounts found.</td></tr>
<?php else: foreach($staffMembers as $staff): ?>
<tr>
<td><div class="staff-name-cell"><strong><?= htmlspecialchars($staff['fullname']) ?></strong><span>@<?= htmlspecialchars($staff['username']) ?></span></div></td>
<td><div class="staff-contact-cell"><span><?= htmlspecialchars($staff['email']??'—') ?></span><small><?= htmlspecialchars($staff['phone_number']??'No phone number') ?></small></div></td>
<td><span class="staff-specialty-badge"><?= htmlspecialchars($staff['specialty']??'Unassigned') ?></span></td>
<td><span class="staff-branch-badge"><?= htmlspecialchars($staff['branch']??'Main Branch') ?></span></td>
<td><span class="staff-employment-badge"><?= htmlspecialchars(ucfirst(str_replace('_',' ',$staff['employment_status']??'full_time'))) ?></span></td>
<td><span class="badge badge-<?= $staff['status']==='active'?'success':'inactive' ?>"><?= ucfirst($staff['status']) ?></span></td>
<td>
<div class="staff-action-group">
<a class="staff-action-btn staff-action-edit" href="edit-staff.php?id=<?= (int)$staff['id'] ?>">Edit</a>
<form method="POST"><input type="hidden" name="staff_id" value="<?= (int)$staff['id'] ?>"><input type="hidden" name="action" value="toggle_status"><button class="staff-action-btn staff-action-status" type="submit"><?= $staff['status']==='active'?'Deactivate':'Activate' ?></button></form>
<form method="POST" onsubmit="return confirm('Delete this staff account?');"><input type="hidden" name="staff_id" value="<?= (int)$staff['id'] ?>"><input type="hidden" name="action" value="delete"><button class="staff-action-btn staff-action-delete" type="submit">Delete</button></form>
</div>
</td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>
</section>

<?php include '../includes/superadmin-footer.php'; ?>
</main></div>
