<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/admin-helper.php';
require_once '../includes/premium-admin-helper.php';
require_once '../includes/branch-context.php';

$pageTitle='Payments';$flash=premiumAdminGetFlash();
$branchId=requireAssignedBranch($pdo);

if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!premiumAdminVerifyCsrf($_POST['csrf_token']??null))premiumAdminFlash('error','Session token expired.','payments.php');
 $id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);$status=$_POST['status']??'pending';
 $reviewNote=trim((string)($_POST['review_note']??''));
 if(!$id||!in_array($status,['pending','verified','rejected'],true))premiumAdminFlash('error','Invalid payment update.','payments.php');
 if($status==='rejected'&&$reviewNote==='')premiumAdminFlash('error','Add a reason when rejecting a payment.','payments.php');
 $pdo->beginTransaction();
 try{
  $lookup=$pdo->prepare("SELECT p.booking_id FROM payments p JOIN bookings b ON b.id=p.booking_id WHERE p.id=? AND b.branch_id=? FOR UPDATE");
  $lookup->execute([$id,$branchId]);$paymentBookingId=(int)$lookup->fetchColumn();
  if($paymentBookingId<=0)throw new RuntimeException('Payment not found for this branch.');
  $pdo->prepare("UPDATE payments SET status=?,review_note=?,verified_at=IF(?='verified',CURRENT_TIMESTAMP,NULL),verified_by=IF(?='verified',?,NULL),updated_at=CURRENT_TIMESTAMP WHERE id=?")
      ->execute([$status,$reviewNote?:null,$status,$status,(int)$_SESSION['user_id'],$id]);
  if($status==='verified'){
   $pdo->prepare("UPDATE bookings SET status='confirmed',updated_at=CURRENT_TIMESTAMP WHERE id=? AND status='pending'")->execute([$paymentBookingId]);
  }elseif($status==='rejected'){
   $pdo->prepare("UPDATE bookings SET status='pending',payment_due_at=DATE_ADD(NOW(),INTERVAL 30 MINUTE),updated_at=CURRENT_TIMESTAMP WHERE id=? AND status<>'cancelled'")->execute([$paymentBookingId]);
  }
  $pdo->commit();
 }catch(Throwable $exception){
  if($pdo->inTransaction())$pdo->rollBack();
  premiumAdminFlash('error','The payment decision could not be saved.','payments.php');
 }
 premiumAdminFlash('success',$status==='verified'?'Payment verified and booking confirmed.':'Payment status updated.','payments.php');
}

$search=trim((string)($_GET['search']??''));$status=trim((string)($_GET['status']??'all'));$where=['b.branch_id=:branch_scope'];$params=['branch_scope'=>$branchId];
if($search!==''){$where[]="(p.reference_number LIKE :reference_search OR c.fullname LIKE :customer_search OR s.name LIKE :service_search)";$v='%'.$search.'%';$params['reference_search']=$v;$params['customer_search']=$v;$params['service_search']=$v;}
if($status!=='all'){$where[]='p.status=:status';$params['status']=$status;}
$stmt=$pdo->prepare("SELECT p.*,b.booking_date,c.fullname AS customer_name,s.name AS service_name FROM payments p LEFT JOIN bookings b ON b.id=p.booking_id LEFT JOIN users c ON c.id=b.customer_id LEFT JOIN services s ON s.id=b.service_id WHERE ".implode(' AND ',$where)." ORDER BY p.created_at DESC");
premiumAdminBind($stmt,$params);$stmt->execute();$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>
<link rel="stylesheet" href="../assets/css/premium-admin-suite.css">
<div class="admin-layout"><?php include '../includes/admin-sidebar.php';?><main class="admin-content">
<header class="admin-page-header premium-admin-header"><div><p class="premium-admin-kicker">Payment verification</p><h1>Payments</h1><p>Review references, proof paths, amounts, and verification status.</p></div></header>
<?php if($flash):?><div class="premium-admin-alert <?=htmlspecialchars($flash['type'])?>"><?=htmlspecialchars($flash['message'])?></div><?php endif;?>
<section class="admin-card premium-admin-toolbar"><div class="premium-admin-toolbar-head"><div><p class="premium-admin-kicker">Payment controls</p><h2>Find a payment</h2><p>Search customer, service, or reference number.</p></div></div><form class="premium-admin-filter compact"><div class="admin-field"><label>Search</label><div class="premium-admin-search"><span>⌕</span><input name="search" value="<?=htmlspecialchars($search)?>" data-live-search="paymentRows" placeholder="Reference, customer, service"></div></div><div class="admin-field"><label>Status</label><select name="status"><?php foreach(['all','pending','verified','rejected'] as $option):?><option value="<?=$option?>" <?=$status===$option?'selected':''?>><?=premiumAdminStatus($option)?></option><?php endforeach;?></select></div><div class="premium-admin-filter-actions"><button class="admin-btn admin-btn-primary">Apply</button><a class="admin-btn admin-btn-light" href="payments.php">Reset</a></div></form></section>
<div class="premium-admin-result-line"><strong><?=count($rows)?></strong><span>payment records</span></div>
<section class="admin-card premium-admin-table-card"><div class="admin-table-wrap"><table class="premium-admin-table"><thead><tr><th>Customer & Service</th><th>Amount</th><th>Method</th><th>Reference</th><th>Proof</th><th>Status</th><th>Action</th></tr></thead><tbody id="paymentRows"><?php if(!$rows):?><tr><td colspan="7"><div class="premium-admin-empty"><span>₱</span><h2>No payments found</h2><p>Submitted payments will appear here.</p></div></td></tr><?php endif;?><?php foreach($rows as $row):?><tr data-search-row="<?=htmlspecialchars(strtolower(($row['customer_name']??'').' '.($row['service_name']??'').' '.($row['reference_number']??'')))?>"><td class="premium-admin-table-main"><strong><?=htmlspecialchars($row['customer_name']??'Unknown customer')?></strong><small><?=htmlspecialchars($row['service_name']??'Unknown service')?> · <?=htmlspecialchars($row['booking_date']??'')?></small></td><td><?=premiumAdminMoney($row['amount'])?></td><td><?=htmlspecialchars($row['payment_method']??'Not specified')?></td><td><?=htmlspecialchars($row['reference_number']??'N/A')?></td><td><?php if(!empty($row['proof_path'])):?><a href="../<?=htmlspecialchars(ltrim($row['proof_path'],'/'))?>" target="_blank">Open proof</a><?php else:?>None<?php endif;?></td><td><span class="premium-admin-badge <?=htmlspecialchars($row['status'])?>"><?=premiumAdminStatus($row['status'])?></span></td><td><div class="premium-admin-row-actions"><button class="primary" data-premium-modal-open="paymentModal" data-payload="<?=htmlspecialchars(json_encode($row),ENT_QUOTES)?>">Review</button></div></td></tr><?php endforeach;?></tbody></table></div></section>
<?php include '../includes/admin-footer.php';?></main></div>
<div class="premium-admin-modal" id="paymentModal"><div class="premium-admin-modal-backdrop" data-premium-modal-close></div><section class="premium-admin-modal-dialog"><header><div><p class="premium-admin-kicker">Verification decision</p><h2>Review Payment</h2></div><button class="premium-admin-modal-close" data-premium-modal-close>×</button></header><form method="POST"><input type="hidden" name="csrf_token" value="<?=premiumAdminCsrf()?>"><input type="hidden" name="id" data-field="id"><div class="admin-field"><label>Status</label><select name="status" data-field="status"><option value="pending">Pending</option><option value="verified">Verified</option><option value="rejected">Rejected</option></select></div><div class="premium-admin-modal-actions"><button type="button" class="admin-btn admin-btn-light" data-premium-modal-close>Cancel</button><button class="admin-btn admin-btn-primary">Save Decision</button></div></form></section></div>
<script src="../assets/js/premium-admin-suite.js"></script>
<script src="../assets/js/admin-payment-verification.js"></script>
