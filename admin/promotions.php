<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/admin-helper.php';
require_once '../includes/premium-admin-helper.php';

$pageTitle='Promotions';$flash=premiumAdminGetFlash();

$pdo->exec("UPDATE promotions SET status='expired' WHERE end_date IS NOT NULL AND end_date<CURDATE() AND status<>'expired'");

if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!premiumAdminVerifyCsrf($_POST['csrf_token']??null))premiumAdminFlash('error','Session token expired.','promotions.php');
 $action=$_POST['action']??'';$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
 if($action==='save'){
  $title=trim((string)($_POST['title']??''));$description=trim((string)($_POST['description']??''));$discount=(float)($_POST['discount_value']??0);$start=$_POST['start_date']?:null;$end=$_POST['end_date']?:null;$status=$_POST['status']??'inactive';
  if($title===''||$discount<0||($start&&$end&&$start>$end))premiumAdminFlash('error','Enter valid promotion details.','promotions.php');
  if($id){$pdo->prepare("UPDATE promotions SET title=?,description=?,discount_value=?,start_date=?,end_date=?,status=? WHERE id=?")->execute([$title,$description?:null,$discount,$start,$end,$status,$id]);}
  else{$pdo->prepare("INSERT INTO promotions(title,description,discount_value,start_date,end_date,status) VALUES(?,?,?,?,?,?)")->execute([$title,$description?:null,$discount,$start,$end,$status]);}
  premiumAdminFlash('success','Promotion saved.','promotions.php');
 }
 if($action==='toggle'&&$id){$pdo->prepare("UPDATE promotions SET status=CASE WHEN status='active' THEN 'inactive' ELSE 'active' END WHERE id=?")->execute([$id]);premiumAdminFlash('success','Promotion status updated.','promotions.php');}
 if($action==='delete'&&$id){$pdo->prepare("DELETE FROM promotions WHERE id=?")->execute([$id]);premiumAdminFlash('success','Promotion removed.','promotions.php');}
}

$search=trim((string)($_GET['search']??''));$status=trim((string)($_GET['status']??'all'));$where=['1=1'];$params=[];
if($search!==''){$where[]='(title LIKE :title_search OR description LIKE :description_search)';$v='%'.$search.'%';$params=['title_search'=>$v,'description_search'=>$v];}
if($status!=='all'){$where[]='status=:status';$params['status']=$status;}
$stmt=$pdo->prepare("SELECT * FROM promotions WHERE ".implode(' AND ',$where)." ORDER BY start_date DESC,id DESC");premiumAdminBind($stmt,$params);$stmt->execute();$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>
<link rel="stylesheet" href="../assets/css/premium-admin-suite.css">
<div class="admin-layout"><?php include '../includes/admin-sidebar.php';?><main class="admin-content">
<header class="admin-page-header premium-admin-header"><div><p class="premium-admin-kicker">Campaign management</p><h1>Promotions</h1><p>Create, schedule, activate, and expire customer offers.</p></div><button class="admin-btn admin-btn-primary" data-premium-modal-open="promotionModal">＋ Add Promotion</button></header>
<?php if($flash):?><div class="premium-admin-alert <?=htmlspecialchars($flash['type'])?>"><?=htmlspecialchars($flash['message'])?></div><?php endif;?>
<section class="admin-card premium-admin-toolbar"><div class="premium-admin-toolbar-head"><div><p class="premium-admin-kicker">Campaign controls</p><h2>Find a promotion</h2><p>Search campaign titles or descriptions.</p></div></div><form class="premium-admin-filter compact"><div class="admin-field"><label>Search</label><div class="premium-admin-search"><span>⌕</span><input name="search" value="<?=htmlspecialchars($search)?>" data-live-search="promotionCards" placeholder="Promotion title"></div></div><div class="admin-field"><label>Status</label><select name="status"><?php foreach(['all','active','inactive','expired'] as $option):?><option value="<?=$option?>" <?=$status===$option?'selected':''?>><?=premiumAdminStatus($option)?></option><?php endforeach;?></select></div><div class="premium-admin-filter-actions"><button class="admin-btn admin-btn-primary">Apply</button><a class="admin-btn admin-btn-light" href="promotions.php">Reset</a></div></form></section>
<div class="premium-admin-result-line"><strong><?=count($rows)?></strong><span>promotions</span></div>
<section class="premium-admin-cards" id="promotionCards"><?php if(!$rows):?><div class="admin-card premium-admin-empty"><span>%</span><h2>No promotions</h2><p>Create a limited-time offer for customers.</p></div><?php endif;?><?php foreach($rows as $row):?><article class="premium-admin-card" data-search-row="<?=htmlspecialchars(strtolower($row['title'].' '.($row['description']??'')))?>"><div style="height:120px;background:radial-gradient(circle at 80% 20%,rgba(240,199,122,.35),transparent 32%),linear-gradient(145deg,#563429,#211511)"></div><div class="premium-admin-card-body"><span class="premium-admin-badge <?=htmlspecialchars($row['status'])?>"><?=premiumAdminStatus($row['status'])?></span><h2><?=htmlspecialchars($row['title'])?></h2><p><?=htmlspecialchars($row['description']??'No campaign description.')?></p><div class="premium-admin-card-meta"><div><small>Discount</small><strong><?=number_format((float)$row['discount_value'],2)?>%</strong></div><div><small>Schedule</small><strong><?=htmlspecialchars(($row['start_date']?:'Open').' – '.($row['end_date']?:'Open'))?></strong></div></div><div class="premium-admin-row-actions" style="margin-top:14px"><button class="primary" data-premium-modal-open="promotionModal" data-payload="<?=htmlspecialchars(json_encode($row),ENT_QUOTES)?>">Edit</button><form method="POST"><input type="hidden" name="csrf_token" value="<?=premiumAdminCsrf()?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?=(int)$row['id']?>"><button><?=$row['status']==='active'?'Deactivate':'Activate'?></button></form><form method="POST" data-premium-confirm="Remove this promotion?"><input type="hidden" name="csrf_token" value="<?=premiumAdminCsrf()?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=(int)$row['id']?>"><button class="danger">Remove</button></form></div></div></article><?php endforeach;?></section>
<?php include '../includes/admin-footer.php';?></main></div>
<div class="premium-admin-modal" id="promotionModal"><div class="premium-admin-modal-backdrop" data-premium-modal-close></div><section class="premium-admin-modal-dialog"><header><div><p class="premium-admin-kicker">Campaign editor</p><h2>Add or Edit Promotion</h2></div><button class="premium-admin-modal-close" data-premium-modal-close>×</button></header><form method="POST"><input type="hidden" name="csrf_token" value="<?=premiumAdminCsrf()?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" data-field="id"><div class="premium-admin-form-grid"><div class="admin-field premium-admin-span-2"><label>Title</label><input name="title" data-field="title" required></div><div class="admin-field premium-admin-span-2"><label>Description</label><textarea name="description" rows="4" data-field="description"></textarea></div><div class="admin-field"><label>Discount percentage</label><input name="discount_value" type="number" min="0" max="100" step=".01" data-field="discount_value" required></div><div class="admin-field"><label>Status</label><select name="status" data-field="status"><option value="active">Active</option><option value="inactive">Inactive</option><option value="expired">Expired</option></select></div><div class="admin-field"><label>Start date</label><input name="start_date" type="date" data-field="start_date"></div><div class="admin-field"><label>End date</label><input name="end_date" type="date" data-field="end_date"></div></div><div class="premium-admin-modal-actions"><button type="button" class="admin-btn admin-btn-light" data-premium-modal-close>Cancel</button><button class="admin-btn admin-btn-primary">Save Promotion</button></div></form></section></div>
<script src="../assets/js/premium-admin-suite.js"></script>
