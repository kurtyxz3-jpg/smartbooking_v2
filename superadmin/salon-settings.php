<?php
declare(strict_types=1);
require_once '../includes/superadmin-auth.php';
require_once '../config/database.php';
require_once '../includes/branch-context.php';

$pageTitle = 'Salon Settings';
$error = '';
$message = '';
$salonId = salonBranchId($pdo);
$load = $pdo->prepare('SELECT * FROM branches WHERE id=?');
$load->execute([$salonId]);
$salon = $load->fetch(PDO::FETCH_ASSOC);
if (empty($_SESSION['salon_settings_csrf'])) {
    $_SESSION['salon_settings_csrf'] = bin2hex(random_bytes(32));
}
$escape = static fn($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['salon_settings_csrf'], $token)) {
        http_response_code(403);
        $error = 'Your session expired. Refresh the page and try again.';
    } else {
        foreach (['address','contact_number','email','opening_time','closing_time','gcash_merchant_name','gcash_number'] as $field) {
            $salon[$field] = trim((string) ($_POST[$field] ?? ''));
        }
        foreach (['address'=>255,'contact_number'=>40,'email'=>150,'gcash_merchant_name'=>150,'gcash_number'=>40] as $field=>$limit) {
            if (mb_strlen($salon[$field]) > $limit) $error = 'One or more details are too long.';
        }
        if ($salon['email'] !== '' && !filter_var($salon['email'], FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email address.';
        }
        if (!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $salon['opening_time'])
            || !preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $salon['closing_time'])
            || $salon['opening_time'] >= $salon['closing_time']) {
            $error = 'Enter valid opening and closing times within the same day.';
        }
        // Retain the stored QR path; never trust a hidden path or posted record ID.
        if ($error === '' && isset($_FILES['gcash_qr']) && $_FILES['gcash_qr']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = $_FILES['gcash_qr'];
            $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
            $mime = $upload['error'] === UPLOAD_ERR_OK
                ? (new finfo(FILEINFO_MIME_TYPE))->file((string) $upload['tmp_name']) : '';
            if ($upload['error'] !== UPLOAD_ERR_OK || !isset($allowed[$mime]) || $upload['size'] > 5 * 1024 * 1024) {
                $error = 'GCash QR must be a JPG, PNG, or WebP image up to 5 MB.';
            } else {
                $directory = __DIR__ . '/../uploads/payment-qr';
                if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
                    $error = 'The payment QR folder could not be created.';
                } else {
                    $filename = 'salon-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
                    if (!move_uploaded_file((string) $upload['tmp_name'], $directory . '/' . $filename)) {
                        $error = 'The GCash QR image could not be saved.';
                    } else {
                        $salon['gcash_qr_path'] = 'uploads/payment-qr/' . $filename;
                    }
                }
            }
        }
        if ($error === '') {
            try {
                $save = $pdo->prepare('UPDATE branches SET address=?,contact_number=?,email=?,opening_time=?,closing_time=?,gcash_merchant_name=?,gcash_number=?,gcash_qr_path=? WHERE id=?');
                $save->execute([$salon['address'] ?: null,$salon['contact_number'] ?: null,$salon['email'] ?: null,
                    $salon['opening_time'],$salon['closing_time'],$salon['gcash_merchant_name'] ?: null,
                    $salon['gcash_number'] ?: null,$salon['gcash_qr_path'] ?: null,$salonId]);
                $message = 'Salon settings updated.';
            } catch (PDOException $exception) {
                $error = 'The settings could not be saved. Please try again.';
            }
        }
    }
}
include '../includes/superadmin-header.php';
?>
<div class="dashboard">
<?php include '../includes/superadmin-sidebar.php'; ?>
<main class="content">
    <div class="page-header"><div><h1 class="gold-title">Salon Settings</h1><p>Contact details, hours, and GCash information for Nails &amp; Body Worx.</p></div></div>
    <?php if ($error !== ''): ?><div class="alert alert-danger"><?= $escape($error) ?></div><?php endif; ?>
    <?php if ($message !== ''): ?><div class="alert alert-success"><?= $escape($message) ?></div><?php endif; ?>
    <section class="card form-card">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['salon_settings_csrf']) ?>">
            <div class="form-grid">
                <div class="form-group form-span-2"><label for="salonAddress">Address</label><input id="salonAddress" name="address" maxlength="255" value="<?= $escape($salon['address']) ?>"></div>
                <div class="form-group"><label for="salonPhone">Contact number</label><input id="salonPhone" name="contact_number" maxlength="40" value="<?= $escape($salon['contact_number']) ?>"></div>
                <div class="form-group"><label for="salonEmail">Email</label><input id="salonEmail" name="email" type="email" maxlength="150" value="<?= $escape($salon['email']) ?>"></div>
                <div class="form-group"><label for="salonOpening">Opening time</label><input id="salonOpening" name="opening_time" type="time" value="<?= $escape(substr((string)$salon['opening_time'],0,5)) ?>" required></div>
                <div class="form-group"><label for="salonClosing">Closing time</label><input id="salonClosing" name="closing_time" type="time" value="<?= $escape(substr((string)$salon['closing_time'],0,5)) ?>" required></div>
                <div class="form-group"><label for="gcashMerchant">GCash merchant name</label><input id="gcashMerchant" name="gcash_merchant_name" maxlength="150" value="<?= $escape($salon['gcash_merchant_name']) ?>"></div>
                <div class="form-group"><label for="gcashNumber">GCash number</label><input id="gcashNumber" name="gcash_number" maxlength="40" value="<?= $escape($salon['gcash_number']) ?>"></div>
                <div class="form-group form-span-2"><label for="gcashQr">GCash QR image</label><input id="gcashQr" name="gcash_qr" type="file" accept="image/jpeg,image/png,image/webp"><small>Leave empty to keep the existing QR image. JPG, PNG, or WebP; maximum 5 MB.</small></div>
            </div>
            <div class="form-actions"><button class="btn btn-gold" type="submit">Save Settings</button></div>
        </form>
    </section>
    <?php include '../includes/superadmin-footer.php'; ?>
</main></div>
