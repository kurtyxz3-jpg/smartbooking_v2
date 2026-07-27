<?php
declare(strict_types=1);

require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
require_once '../includes/branch-context.php';

$pageTitle = 'Branches';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? 'save');
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($action === 'toggle' && $id) {
        $pdo->prepare(
            "UPDATE branches
             SET status = CASE WHEN status='active' THEN 'inactive' ELSE 'active' END
             WHERE id = ?"
        )->execute([$id]);
        $message = 'Branch status updated.';
    } elseif ($action === 'save') {
        $code = strtoupper(trim((string) ($_POST['branch_code'] ?? '')));
        $name = trim((string) ($_POST['branch_name'] ?? ''));
        $address = trim((string) ($_POST['address'] ?? ''));
        $phone = trim((string) ($_POST['contact_number'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $opening = (string) ($_POST['opening_time'] ?? '09:00');
        $closing = (string) ($_POST['closing_time'] ?? '20:00');
        $gcashMerchant = trim((string) ($_POST['gcash_merchant_name'] ?? ''));
        $gcashNumber = trim((string) ($_POST['gcash_number'] ?? ''));
        $gcashQrPath = trim((string) ($_POST['existing_gcash_qr_path'] ?? ''));

        if (!empty($_FILES['gcash_qr']['name'])) {
            $upload = $_FILES['gcash_qr'];
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $mime = ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK
                ? (new finfo(FILEINFO_MIME_TYPE))->file((string) $upload['tmp_name'])
                : '';

            if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
                || !isset($allowed[$mime])
                || (int) $upload['size'] > 5 * 1024 * 1024) {
                $error = 'GCash QR must be a JPG, PNG, or WEBP image up to 5 MB.';
            } else {
                $directory = __DIR__ . '/../uploads/payment-qr';
                if (!is_dir($directory)) {
                    mkdir($directory, 0775, true);
                }
                $filename = 'branch-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
                if (move_uploaded_file((string) $upload['tmp_name'], $directory . '/' . $filename)) {
                    $gcashQrPath = 'uploads/payment-qr/' . $filename;
                } else {
                    $error = 'The GCash QR image could not be saved.';
                }
            }
        }

        if ($error !== '') {
            // Keep the upload validation message.
        } elseif ($code === '' || $name === '') {
            $error = 'Branch code and name are required.';
        } elseif ($id) {
            $statement = $pdo->prepare(
                "UPDATE branches SET branch_code=?, branch_name=?, address=?,
                 contact_number=?, email=?, opening_time=?, closing_time=?,
                 gcash_merchant_name=?, gcash_number=?, gcash_qr_path=?
                 WHERE id=?"
            );
            $statement->execute([$code,$name,$address?:null,$phone?:null,$email?:null,$opening,$closing,$gcashMerchant?:null,$gcashNumber?:null,$gcashQrPath?:null,$id]);
            $message = 'Branch details updated.';
        } else {
            $statement = $pdo->prepare(
                 "INSERT INTO branches
                 (branch_code,branch_name,address,contact_number,email,opening_time,closing_time,
                  gcash_merchant_name,gcash_number,gcash_qr_path,display_order)
                 VALUES (?,?,?,?,?,?,?,?,?,?,(SELECT COALESCE(MAX(b.display_order),0)+1 FROM branches b))"
            );
            $statement->execute([$code,$name,$address?:null,$phone?:null,$email?:null,$opening,$closing,$gcashMerchant?:null,$gcashNumber?:null,$gcashQrPath?:null]);
            $message = 'Branch added.';
        }
    }
}

$branches = $pdo->query(
    "SELECT b.*,
            SUM(u.role='admin') AS admins,
            SUM(u.role='staff') AS staff
     FROM branches b
     LEFT JOIN users u ON u.branch_id=b.id
     GROUP BY b.id
     ORDER BY b.display_order,b.branch_name"
)->fetchAll(PDO::FETCH_ASSOC);

include '../includes/superadmin-header.php';
?>
<link rel="stylesheet" href="../assets/css/branch-management.css">
<div class="dashboard">
<?php include '../includes/superadmin-sidebar.php'; ?>
<main class="content">
  <header class="page-header branch-page-header">
    <div><h1>Branches</h1><p>Manage the salon locations used by accounts, bookings, inventory, and reports.</p></div>
    <button class="btn btn-gold" type="button" data-branch-open>Add Branch</button>
  </header>
  <?php if ($message): ?><div class="branch-alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="branch-alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <section class="branch-grid">
  <?php foreach ($branches as $branch): ?>
    <article class="card branch-card">
      <div class="branch-card-top">
        <span class="branch-status <?= htmlspecialchars($branch['status']) ?>"><?= ucfirst($branch['status']) ?></span>
        <small><?= htmlspecialchars($branch['branch_code']) ?></small>
      </div>
      <h2><?= htmlspecialchars($branch['branch_name']) ?></h2>
      <p><?= htmlspecialchars($branch['address'] ?: 'Address not yet configured') ?></p>
      <div class="branch-metrics"><span><b><?= (int)$branch['admins'] ?></b> Admins</span><span><b><?= (int)$branch['staff'] ?></b> Staff</span></div>
      <div class="branch-actions">
        <button type="button" class="btn btn-light" data-branch-edit='<?= htmlspecialchars(json_encode($branch), ENT_QUOTES) ?>'>Edit</button>
        <form method="post"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$branch['id'] ?>"><button class="btn btn-light"><?= $branch['status']==='active'?'Deactivate':'Activate' ?></button></form>
      </div>
    </article>
  <?php endforeach; ?>
  </section>
  <?php include '../includes/superadmin-footer.php'; ?>
</main></div>

<dialog id="branchDialog" class="branch-dialog">
<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="action" value="save"><input type="hidden" name="id" id="branchId">
  <input type="hidden" name="existing_gcash_qr_path" id="branchGcashQrPath">
  <div class="branch-dialog-head"><h2 id="branchDialogTitle">Add Branch</h2><button type="button" data-branch-close>×</button></div>
  <div class="branch-form-grid">
    <label>Branch code<input name="branch_code" id="branchCode" required></label>
    <label>Branch name<input name="branch_name" id="branchName" required></label>
    <label class="wide">Address<input name="address" id="branchAddress"></label>
    <label>Contact number<input name="contact_number" id="branchPhone"></label>
    <label>Email<input type="email" name="email" id="branchEmail"></label>
    <label>Opening time<input type="time" name="opening_time" id="branchOpening" value="09:00"></label>
    <label>Closing time<input type="time" name="closing_time" id="branchClosing" value="20:00"></label>
    <label>GCash merchant name<input name="gcash_merchant_name" id="branchGcashMerchant" placeholder="Official merchant name"></label>
    <label>GCash number<input name="gcash_number" id="branchGcashNumber" placeholder="09XXXXXXXXX"></label>
    <label class="wide">Official GCash QR image<input type="file" name="gcash_qr" accept=".jpg,.jpeg,.png,.webp"><small>Leave blank to keep the current QR image.</small></label>
  </div>
  <div class="branch-dialog-actions"><button type="button" class="btn btn-light" data-branch-close>Cancel</button><button class="btn btn-gold">Save Branch</button></div>
</form></dialog>
<script src="../assets/js/branch-management.js"></script>
