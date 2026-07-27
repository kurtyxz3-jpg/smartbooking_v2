<?php
declare(strict_types=1);
require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
require_once '../includes/branch-context.php';
$branches=activeBranches($pdo);
$branchId=0;

$isEdit = false;
$pageTitle = $isEdit ? 'Edit Staff' : 'Add Staff';
$error = '';

$specialtyOptions = [
    'Nail Technician','Senior Nail Technician','Receptionist','Cashier',
    'Massage Therapist','Waxing Specialist','Eyelash Technician','Beauty Therapist'
];

$staffId = $isEdit ? filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT) : null;
$fullname=''; $username=''; $email=''; $phoneNumber=''; $specialty='';
$branch='Main Branch'; $employmentStatus='full_time'; $status='active';

if($isEdit){
    if(!$staffId){header('Location: staff.php');exit;}
    $stmt=$pdo->prepare("SELECT u.fullname,u.username,u.email,u.phone_number,u.status,
                               sp.specialty,sp.branch,sp.employment_status,u.branch_id
                        FROM users u
                        LEFT JOIN staff_profiles sp ON sp.user_id=u.id
                        WHERE u.id=? AND u.role='staff' LIMIT 1");
    $stmt->execute([$staffId]);
    $staff=$stmt->fetch();
    if(!$staff){header('Location: staff.php');exit;}

    $fullname=$staff['fullname'];
    $username=$staff['username'];
    $email=$staff['email'];
    $phoneNumber=$staff['phone_number']??'';
    $status=$staff['status'];
    $specialty=$staff['specialty']??'';
    $branch=$staff['branch']??'Main Branch';
    $branchId=(int)($staff['branch_id']??0);
    $employmentStatus=$staff['employment_status']??'full_time';
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $fullname=trim($_POST['fullname']??'');
    $username=trim($_POST['username']??'');
    $email=trim($_POST['email']??'');
    $phoneNumber=trim($_POST['phone_number']??'');
    $status=$_POST['status']??'active';
    $specialty=trim($_POST['specialty']??'');
    $branch=trim($_POST['branch']??'Main Branch');
    $branchId=(int)($_POST['branch_id']??0);
    $employmentStatus=$_POST['employment_status']??'full_time';

    $password=$isEdit?($_POST['new_password']??''):($_POST['password']??'');
    $confirmPassword=$_POST['confirm_password']??'';

    if($fullname===''||$username===''||$email===''||(!$isEdit&&$password==='')){
        $error='Please complete all required fields.';
    }elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
        $error='Please enter a valid email address.';
    }elseif($password!==''&&strlen($password)<6){
        $error='Password must contain at least 6 characters.';
    }elseif($password!==$confirmPassword){
        $error='Passwords do not match.';
    }elseif(!in_array($status,['active','inactive'],true)){
        $error='Invalid account status.';
    }elseif(!in_array($employmentStatus,['full_time','part_time','contractual'],true)){
        $error='Invalid employment status.';
    }else{
        $checkSql="SELECT id FROM users WHERE (username=:username OR email=:email)";
        if($isEdit){$checkSql.=" AND id!=:id";}
        $checkSql.=" LIMIT 1";
        $check=$pdo->prepare($checkSql);
        $checkParams=['username'=>$username,'email'=>$email];
        if($isEdit){$checkParams['id']=$staffId;}
        $check->execute($checkParams);

        if($check->fetch()){
            $error='The username or email address is already in use.';
        }else{
            $pdo->beginTransaction();
            try{
                if($isEdit){
                    if($password!==''){
                        $stmt=$pdo->prepare("UPDATE users SET fullname=:fullname,username=:username,email=:email,phone_number=:phone,password=:password,status=:status,branch_id=:branch_id WHERE id=:id AND role='staff'");
                        $stmt->execute(['fullname'=>$fullname,'username'=>$username,'email'=>$email,'phone'=>$phoneNumber,'password'=>password_hash($password,PASSWORD_DEFAULT),'status'=>$status,'branch_id'=>$branchId,'id'=>$staffId]);
                    }else{
                        $stmt=$pdo->prepare("UPDATE users SET fullname=:fullname,username=:username,email=:email,phone_number=:phone,status=:status,branch_id=:branch_id WHERE id=:id AND role='staff'");
                        $stmt->execute(['fullname'=>$fullname,'username'=>$username,'email'=>$email,'phone'=>$phoneNumber,'status'=>$status,'branch_id'=>$branchId,'id'=>$staffId]);
                    }
                    $userId=$staffId;
                }else{
                    $stmt=$pdo->prepare("INSERT INTO users(fullname,username,email,phone_number,password,role,status,branch_id) VALUES(:fullname,:username,:email,:phone,:password,'staff',:status,:branch_id)");
                    $stmt->execute(['fullname'=>$fullname,'username'=>$username,'email'=>$email,'phone'=>$phoneNumber,'password'=>password_hash($password,PASSWORD_DEFAULT),'status'=>$status,'branch_id'=>$branchId]);
                    $userId=(int)$pdo->lastInsertId();
                }

                $profile=$pdo->prepare("INSERT INTO staff_profiles(user_id,specialty,branch,employment_status)
                                       VALUES(:user_id,:specialty,:branch,:employment_status)
                                       ON DUPLICATE KEY UPDATE specialty=VALUES(specialty),branch=VALUES(branch),employment_status=VALUES(employment_status)");
                $profile->execute(['user_id'=>$userId,'specialty'=>$specialty,'branch'=>$branch,'employment_status'=>$employmentStatus]);

                $pdo->commit();
                header('Location: staff.php');
                exit;
            }catch(Throwable $e){
                if($pdo->inTransaction()){$pdo->rollBack();}
                $error=$isEdit?'Unable to update the staff account.':'Unable to create the staff account.';
            }
        }
    }
}

include '../includes/superadmin-header.php';
?>
<div class="dashboard"><?php include '../includes/superadmin-sidebar.php'; ?>
<main class="content">
<div class="page-header"><div><h1 class="gold-title"><?= $isEdit?'Edit Staff':'Add Staff' ?></h1><p><?= $isEdit?'Update account access and employment details.':'Create a staff login account and employment profile.' ?></p></div><a class="btn btn-light" href="staff.php">Back to Staff</a></div>

<?php if($error!==''): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<section class="card staff-form-card">
<form method="POST">

<div class="staff-form-section">
<h2>Account Information</h2>
<div class="staff-form-grid">
<div class="form-group staff-span-2"><label for="fullname">Full Name</label><input id="fullname" name="fullname" value="<?= htmlspecialchars($fullname) ?>" required></div>
<div class="form-group"><label for="username">Username</label><input id="username" name="username" value="<?= htmlspecialchars($username) ?>" required></div>
<div class="form-group"><label for="email">Email Address</label><input id="email" name="email" type="email" value="<?= htmlspecialchars($email) ?>" required></div>
<div class="form-group"><label for="phone_number">Phone Number</label><input id="phone_number" name="phone_number" value="<?= htmlspecialchars($phoneNumber) ?>"></div>
<div class="form-group"><label for="status">Account Status</label><select id="status" name="status"><option value="active" <?= $status==='active'?'selected':'' ?>>Active</option><option value="inactive" <?= $status==='inactive'?'selected':'' ?>>Inactive</option></select></div>
</div>
</div>

<div class="staff-form-section">
<h2>Employment Details</h2>
<div class="staff-form-grid">
<div class="form-group"><label for="specialty">Specialty</label><select id="specialty" name="specialty"><option value="">Select specialty</option><?php foreach($specialtyOptions as $option): ?><option value="<?= htmlspecialchars($option) ?>" <?= $specialty===$option?'selected':'' ?>><?= htmlspecialchars($option) ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label for="branch_id">Branch</label><select id="branch_id" name="branch_id" required><option value="">Select branch</option><?php foreach($branches as $branchOption): ?><option value="<?= (int)$branchOption['id'] ?>" <?= $branchId===(int)$branchOption['id']?'selected':'' ?>><?= htmlspecialchars($branchOption['branch_name']) ?></option><?php endforeach; ?></select><input type="hidden" name="branch" value="<?= htmlspecialchars($branch) ?>"></div>
<div class="form-group"><label for="employment_status">Employment Status</label><select id="employment_status" name="employment_status"><option value="full_time" <?= $employmentStatus==='full_time'?'selected':'' ?>>Full Time</option><option value="part_time" <?= $employmentStatus==='part_time'?'selected':'' ?>>Part Time</option><option value="contractual" <?= $employmentStatus==='contractual'?'selected':'' ?>>Contractual</option></select></div>
</div>
</div>

<div class="staff-form-section">
<h2><?= $isEdit?'Optional Password Reset':'Login Password' ?></h2>
<div class="staff-form-grid">
<div class="form-group"><label for="<?= $isEdit?'new_password':'password' ?>"><?= $isEdit?'New Password':'Password' ?></label><input id="<?= $isEdit?'new_password':'password' ?>" name="<?= $isEdit?'new_password':'password' ?>" type="password" <?= $isEdit?'placeholder="Leave blank to keep current password"':'required' ?>></div>
<div class="form-group"><label for="confirm_password">Confirm Password</label><input id="confirm_password" name="confirm_password" type="password" <?= $isEdit?'':'required' ?>></div>
</div>
</div>

<div class="form-actions"><button class="btn btn-gold" type="submit"><?= $isEdit?'Save Changes':'Create Staff Account' ?></button><a class="btn btn-light" href="staff.php">Cancel</a></div>
</form>
</section>

<?php include '../includes/superadmin-footer.php'; ?>
</main></div>
