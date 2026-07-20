<?php
declare(strict_types=1);
require_once '../includes/superadmin-auth.php';
$pageTitle='Staff Roles';
$roles=[
['Nail Technician','Performs manicure, pedicure, gel polish, and nail art services.','Assigned bookings, customer service details, and personal performance.'],
['Receptionist','Assists customers and coordinates appointments.','Bookings, customer records, and appointment confirmations.'],
['Cashier','Handles payment records and customer receipts.','Payments, booking totals, and receipt confirmation.'],
['Massage Therapist','Provides wellness and massage services.','Assigned bookings, service notes, and personal schedule.'],
['Beauty Therapist','Provides beauty and body care treatments.','Assigned bookings, service notes, and personal performance.']
];
include '../includes/superadmin-header.php';
?>
<div class="dashboard"><?php include '../includes/superadmin-sidebar.php'; ?>
<main class="content">
<div class="page-header"><div><h1 class="gold-title">Staff Roles</h1><p>Review responsibilities and intended system access.</p></div><a class="btn btn-light" href="staff.php">Back to Staff</a></div>
<section class="staff-role-grid">
<?php foreach($roles as $role): ?>
<article class="card staff-role-card"><span>Staff Role</span><h2><?= htmlspecialchars($role[0]) ?></h2><p><?= htmlspecialchars($role[1]) ?></p><div class="staff-role-access"><strong>Recommended access</strong><p><?= htmlspecialchars($role[2]) ?></p></div></article>
<?php endforeach; ?>
</section>
<?php include '../includes/superadmin-footer.php'; ?>
</main></div>
