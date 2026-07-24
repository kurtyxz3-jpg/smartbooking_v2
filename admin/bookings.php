<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/admin-helper.php';
require_once '../includes/premium-admin-helper.php';

$pageTitle = 'Bookings';
$flash = premiumAdminGetFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!premiumAdminVerifyCsrf($_POST['csrf_token'] ?? null)) {
        premiumAdminFlash('error', 'Session token expired.', 'bookings.php');
    }

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $status = trim((string) ($_POST['status'] ?? 'pending'));
    $staffId = filter_input(INPUT_POST, 'staff_id', FILTER_VALIDATE_INT);
    $notes = trim((string) ($_POST['notes'] ?? ''));

    if (!$id || !in_array($status, ['pending','confirmed','completed','cancelled','no_show'], true)) {
        premiumAdminFlash('error', 'Invalid booking update.', 'bookings.php');
    }

    if ($staffId) {
        $booking = $pdo->prepare("SELECT booking_date, booking_time FROM bookings WHERE id = ?");
        $booking->execute([$id]);
        $schedule = $booking->fetch(PDO::FETCH_ASSOC);

        $conflict = $pdo->prepare(
            "SELECT COUNT(*) FROM bookings
             WHERE id <> ?
               AND staff_id = ?
               AND booking_date = ?
               AND booking_time = ?
               AND status IN ('pending','confirmed')"
        );
        $conflict->execute([$id, $staffId, $schedule['booking_date'], $schedule['booking_time']]);

        if ((int) $conflict->fetchColumn() > 0) {
            premiumAdminFlash('error', 'That staff member already has an appointment at this time.', 'bookings.php');
        }
    }

    $statement = $pdo->prepare(
        "UPDATE bookings
         SET staff_id = ?, status = ?, notes = ?
         WHERE id = ?"
    );
    $statement->execute([$staffId ?: null, $status, $notes ?: null, $id]);

    premiumAdminFlash('success', 'Booking updated.', 'bookings.php');
}

$search = trim((string) ($_GET['search'] ?? ''));
$status = trim((string) ($_GET['status'] ?? 'all'));
$date = trim((string) ($_GET['date'] ?? ''));
$staffFilter = filter_input(INPUT_GET, 'staff', FILTER_VALIDATE_INT);

$where = ['1=1'];
$params = [];

if ($search !== '') {
    $where[] = "(CAST(b.id AS CHAR) LIKE :id_search OR c.fullname LIKE :customer_search OR s.name LIKE :service_search)";
    $value = '%' . $search . '%';
    $params = ['id_search'=>$value,'customer_search'=>$value,'service_search'=>$value];
}

if ($status !== 'all') {
    $where[] = 'b.status = :status';
    $params['status'] = $status;
}

if ($date !== '') {
    $where[] = 'b.booking_date = :booking_date';
    $params['booking_date'] = $date;
}

if ($staffFilter) {
    $where[] = 'b.staff_id = :staff_id';
    $params['staff_id'] = $staffFilter;
}

$whereSql = implode(' AND ', $where);

$statement = $pdo->prepare(
    "SELECT b.*, c.fullname AS customer_name, s.name AS service_name,
            s.price AS service_price, st.fullname AS staff_name,
            p.status AS payment_status
     FROM bookings b
     LEFT JOIN users c ON c.id = b.customer_id
     LEFT JOIN services s ON s.id = b.service_id
     LEFT JOIN users st ON st.id = b.staff_id
     LEFT JOIN payments p ON p.booking_id = b.id
     WHERE {$whereSql}
     ORDER BY b.booking_date DESC, b.booking_time DESC
     LIMIT 150"
);
premiumAdminBind($statement, $params);
$statement->execute();
$rows = $statement->fetchAll(PDO::FETCH_ASSOC);

$staff = $pdo->query(
    "SELECT id, fullname FROM users
     WHERE role='staff' AND status='active'
     ORDER BY fullname"
)->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>
<link rel="stylesheet" href="../assets/css/premium-admin-suite.css">
<div class="admin-layout">
<?php include '../includes/admin-sidebar.php'; ?>
<main class="admin-content">
<header class="admin-page-header premium-admin-header">
<div><p class="premium-admin-kicker">Appointment operations</p><h1>Bookings</h1><p>Search, assign professionals, update status, and prevent schedule conflicts.</p></div>
</header>
<?php if ($flash): ?><div class="premium-admin-alert <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div><?php endif; ?>

<section class="admin-card premium-admin-toolbar">
<div class="premium-admin-toolbar-head"><div><p class="premium-admin-kicker">Booking controls</p><h2>Find an appointment</h2><p>Use filters or type a customer, service, or booking ID.</p></div></div>
<form method="GET" class="premium-admin-filter compact">
<div class="admin-field"><label>Search</label><div class="premium-admin-search"><span>⌕</span><input name="search" value="<?= htmlspecialchars($search) ?>" data-live-search="bookingRows" placeholder="Customer, service, or ID"></div></div>
<div class="admin-field"><label>Status</label><select name="status"><?php foreach (['all','pending','confirmed','completed','cancelled','no_show'] as $option): ?><option value="<?= $option ?>" <?= $status===$option?'selected':'' ?>><?= premiumAdminStatus($option) ?></option><?php endforeach; ?></select></div>
<div class="admin-field"><label>Date</label><input type="date" name="date" value="<?= htmlspecialchars($date) ?>"></div>
<div class="admin-field"><label>Staff</label><select name="staff"><option value="">All staff</option><?php foreach ($staff as $member): ?><option value="<?= (int)$member['id'] ?>" <?= $staffFilter===(int)$member['id']?'selected':'' ?>><?= htmlspecialchars($member['fullname']) ?></option><?php endforeach; ?></select></div>
<div class="premium-admin-filter-actions"><button class="admin-btn admin-btn-primary">Apply Filters</button><a class="admin-btn admin-btn-light" href="bookings.php">Reset</a></div>
</form>
</section>

<div class="premium-admin-result-line"><strong><?= count($rows) ?></strong><span>booking records</span></div>
<section class="admin-card premium-admin-table-card">
<div class="admin-table-wrap">
<table class="premium-admin-table">
<thead><tr><th>Booking</th><th>Customer & Service</th><th>Schedule</th><th>Staff</th><th>Payment</th><th>Status</th><th>Action</th></tr></thead>
<tbody id="bookingRows">
<?php if (!$rows): ?><tr><td colspan="7"><div class="premium-admin-empty"><span>◇</span><h2>No bookings found</h2><p>Try another filter or wait for a customer booking.</p></div></td></tr><?php endif; ?>
<?php foreach ($rows as $row): ?>
<tr data-search-row="<?= htmlspecialchars(strtolower(($row['id'].' '.($row['customer_name']??'').' '.($row['service_name']??'')))) ?>">
<td>#<?= (int)$row['id'] ?></td>
<td class="premium-admin-table-main"><strong><?= htmlspecialchars($row['customer_name']??'Guest customer') ?></strong><small><?= htmlspecialchars($row['service_name']??'Unknown service') ?> · <?= premiumAdminMoney($row['service_price']??0) ?></small></td>
<td><?= date('M j, Y',strtotime($row['booking_date'])) ?><br><small><?= date('g:i A',strtotime($row['booking_time'])) ?></small></td>
<td><?= htmlspecialchars($row['staff_name']??'Unassigned') ?></td>
<td><span class="premium-admin-badge <?= htmlspecialchars($row['payment_status']??'pending') ?>"><?= premiumAdminStatus($row['payment_status']??'not_submitted') ?></span></td>
<td><span class="premium-admin-badge <?= htmlspecialchars($row['status']) ?>"><?= premiumAdminStatus($row['status']) ?></span></td>
<td><div class="premium-admin-row-actions"><button class="primary" data-premium-modal-open="bookingModal" data-payload="<?= htmlspecialchars(json_encode($row),ENT_QUOTES) ?>">Manage</button></div></td>
</tr>
<?php endforeach; ?>
</tbody></table></div></section>
<?php include '../includes/admin-footer.php'; ?>
</main></div>

<div class="premium-admin-modal" id="bookingModal" aria-hidden="true"><div class="premium-admin-modal-backdrop" data-premium-modal-close></div><section class="premium-admin-modal-dialog"><header><div><p class="premium-admin-kicker">Appointment update</p><h2>Manage Booking</h2></div><button class="premium-admin-modal-close" data-premium-modal-close>×</button></header><form method="POST"><input type="hidden" name="csrf_token" value="<?= premiumAdminCsrf() ?>"><input type="hidden" name="id" data-field="id"><div class="premium-admin-form-grid"><div class="admin-field"><label>Assigned staff</label><select name="staff_id" data-field="staff_id"><option value="">Unassigned</option><?php foreach ($staff as $member): ?><option value="<?= (int)$member['id'] ?>"><?= htmlspecialchars($member['fullname']) ?></option><?php endforeach; ?></select></div><div class="admin-field"><label>Status</label><select name="status" data-field="status"><?php foreach (['pending','confirmed','completed','cancelled','no_show'] as $option): ?><option value="<?= $option ?>"><?= premiumAdminStatus($option) ?></option><?php endforeach; ?></select></div><div class="admin-field premium-admin-span-2"><label>Notes</label><textarea name="notes" rows="4" data-field="notes"></textarea></div></div><div class="premium-admin-modal-actions"><button type="button" class="admin-btn admin-btn-light" data-premium-modal-close>Cancel</button><button class="admin-btn admin-btn-primary">Save Booking</button></div></form></section></div>
<script src="../assets/js/premium-admin-suite.js"></script>
