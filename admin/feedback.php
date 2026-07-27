<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/admin-helper.php';
require_once '../includes/premium-admin-helper.php';
require_once '../includes/branch-context.php';

$pageTitle='Feedback';$flash=premiumAdminGetFlash();
$branchId=requireAssignedBranch($pdo);

if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!premiumAdminVerifyCsrf($_POST['csrf_token']??null))premiumAdminFlash('error','Session token expired.','feedback.php');
 $id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);$status=$_POST['status']??'reviewed';
 if(!$id||!in_array($status,['new','reviewed','flagged'],true))premiumAdminFlash('error','Invalid feedback update.','feedback.php');
 $pdo->prepare("UPDATE feedback f JOIN bookings b ON b.id=f.booking_id SET f.status=? WHERE f.id=? AND b.branch_id=?")->execute([$status,$id,$branchId]);
 premiumAdminFlash('success','Feedback status updated.','feedback.php');
}

$search=trim((string)($_GET['search']??''));$status=trim((string)($_GET['status']??'all'));$rating=filter_input(INPUT_GET,'rating',FILTER_VALIDATE_INT);$where=['b.branch_id=:branch_scope'];$params=['branch_scope'=>$branchId];
if($search!==''){$where[]="(c.fullname LIKE :customer_search OR f.comment LIKE :comment_search OR s.name LIKE :service_search)";$v='%'.$search.'%';$params=['customer_search'=>$v,'comment_search'=>$v,'service_search'=>$v];}
if($status!=='all'){$where[]='f.status=:status';$params['status']=$status;}
if($rating){$where[]='f.rating=:rating';$params['rating']=$rating;}
$stmt=$pdo->prepare("SELECT f.*,c.fullname AS customer_name,s.name AS service_name,b.booking_date FROM feedback f LEFT JOIN users c ON c.id=f.customer_id LEFT JOIN bookings b ON b.id=f.booking_id LEFT JOIN services s ON s.id=b.service_id WHERE ".implode(' AND ',$where)." ORDER BY f.created_at DESC");
premiumAdminBind($stmt,$params);$stmt->execute();$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>
<link rel="stylesheet" href="../assets/css/premium-admin-suite.css">
<div class="admin-layout"><?php include '../includes/admin-sidebar.php';?><main class="admin-content">
<header class="admin-page-header premium-admin-header"><div><p class="premium-admin-kicker">Customer experience</p><h1>Feedback</h1><p>Review ratings, comments, and items that need follow-up.</p></div></header>
<?php if($flash):?><div class="premium-admin-alert <?=htmlspecialchars($flash['type'])?>"><?=htmlspecialchars($flash['message'])?></div><?php endif;?>
<section class="admin-card premium-admin-toolbar"><div class="premium-admin-toolbar-head"><div><p class="premium-admin-kicker">Feedback controls</p><h2>Find customer feedback</h2><p>Search by customer, service, or comment.</p></div></div><form class="premium-admin-filter compact"><div class="admin-field"><label>Search</label><div class="premium-admin-search"><span>⌕</span><input name="search" value="<?=htmlspecialchars($search)?>" data-live-search="feedbackRows" placeholder="Customer, service, comment"></div></div><div class="admin-field"><label>Status</label><select name="status"><?php foreach(['all','new','reviewed','flagged'] as $option):?><option value="<?=$option?>" <?=$status===$option?'selected':''?>><?=premiumAdminStatus($option)?></option><?php endforeach;?></select></div><div class="admin-field"><label>Rating</label><select name="rating"><option value="">All ratings</option><?php for($i=5;$i>=1;$i--):?><option value="<?=$i?>" <?=$rating===$i?'selected':''?>><?=$i?> stars</option><?php endfor;?></select></div><div class="premium-admin-filter-actions"><button class="admin-btn admin-btn-primary">Apply</button><a class="admin-btn admin-btn-light" href="feedback.php">Reset</a></div></form></section>
<div class="premium-admin-result-line"><strong><?=count($rows)?></strong><span>feedback entries</span></div>
<section class="admin-card premium-admin-table-card"><div class="admin-table-wrap"><table class="premium-admin-table"><thead><tr><th>Customer & Service</th><th>Rating</th><th>Comment</th><th>Date</th><th>Status</th><th>Action</th></tr></thead><tbody id="feedbackRows"><?php if(!$rows):?><tr><td colspan="6"><div class="premium-admin-empty"><span>★</span><h2>No feedback found</h2><p>Completed appointment reviews will appear here.</p></div></td></tr><?php endif;?><?php foreach($rows as $row):?><tr data-search-row="<?=htmlspecialchars(strtolower(($row['customer_name']??'').' '.($row['service_name']??'').' '.($row['comment']??'')))?>"><td class="premium-admin-table-main"><strong><?=htmlspecialchars($row['customer_name']??'Anonymous customer')?></strong><small><?=htmlspecialchars($row['service_name']??'Unknown service')?></small></td><td><?=str_repeat('★',(int)$row['rating'])?><?=str_repeat('☆',5-(int)$row['rating'])?></td><td style="max-width:380px"><?=htmlspecialchars($row['comment']??'No comment')?></td><td><?=date('M j, Y',strtotime($row['created_at']))?></td><td><span class="premium-admin-badge <?=htmlspecialchars($row['status'])?>"><?=premiumAdminStatus($row['status'])?></span></td><td><div class="premium-admin-row-actions"><button class="primary" data-premium-modal-open="feedbackModal" data-payload="<?=htmlspecialchars(json_encode($row),ENT_QUOTES)?>">Review</button></div></td></tr><?php endforeach;?></tbody></table></div></section>
<?php include '../includes/admin-footer.php';?></main></div>
<div class="premium-admin-modal" id="feedbackModal"><div class="premium-admin-modal-backdrop" data-premium-modal-close></div><section class="premium-admin-modal-dialog"><header><div><p class="premium-admin-kicker">Feedback review</p><h2>Update Status</h2></div><button class="premium-admin-modal-close" data-premium-modal-close>×</button></header><form method="POST"><input type="hidden" name="csrf_token" value="<?=premiumAdminCsrf()?>"><input type="hidden" name="id" data-field="id"><div class="admin-field"><label>Status</label><select name="status" data-field="status"><option value="new">New</option><option value="reviewed">Reviewed</option><option value="flagged">Flagged</option></select></div><div class="premium-admin-modal-actions"><button type="button" class="admin-btn admin-btn-light" data-premium-modal-close>Cancel</button><button class="admin-btn admin-btn-primary">Save Status</button></div></form></section></div>
<script src="../assets/js/premium-admin-suite.js"></script>
