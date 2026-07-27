<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/admin-helper.php';
require_once '../includes/premium-admin-helper.php';
require_once '../includes/branch-context.php';

$pageTitle = 'Inventory';
$flash = premiumAdminGetFlash();
$branchId = requireAssignedBranch($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!premiumAdminVerifyCsrf($_POST['csrf_token'] ?? null)) {
        premiumAdminFlash('error','Session token expired.','inventory.php');
    }

    $action = $_POST['action'] ?? '';
    $id = filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);

    if ($action === 'save') {
        $name = trim((string)($_POST['item_name']??''));
        $quantity = (float)($_POST['quantity']??0);
        $reorder = (float)($_POST['reorder_level']??0);
        $unit = trim((string)($_POST['unit']??'unit'));

        if ($name === '' || $quantity < 0 || $reorder < 0) {
            premiumAdminFlash('error','Enter valid inventory details.','inventory.php');
        }

        $status = $quantity <= 0 ? 'out_of_stock' : ($quantity <= $reorder ? 'low_stock' : 'in_stock');

        if ($id) {
            $statement=$pdo->prepare("UPDATE inventory SET item_name=?,quantity=?,reorder_level=?,unit=?,status=? WHERE id=? AND branch_id=?");
            $statement->execute([$name,$quantity,$reorder,$unit,$status,$id,$branchId]);
        } else {
            $statement=$pdo->prepare("INSERT INTO inventory(item_name,quantity,reorder_level,unit,status,branch_id) VALUES(?,?,?,?,?,?)");
            $statement->execute([$name,$quantity,$reorder,$unit,$status,$branchId]);
        }

        premiumAdminFlash('success','Inventory item saved.','inventory.php');
    }

    if ($action === 'adjust' && $id) {
        $type = $_POST['adjustment_type'] ?? 'in';
        $amount = (float)($_POST['amount']??0);
        $note = trim((string)($_POST['note']??''));

        if ($amount <= 0) premiumAdminFlash('error','Adjustment amount must be greater than zero.','inventory.php');

        $item=$pdo->prepare("SELECT * FROM inventory WHERE id=? AND branch_id=?");
        $item->execute([$id,$branchId]);
        $row=$item->fetch(PDO::FETCH_ASSOC);

        $newQuantity = $type==='out'
            ? max(0,(float)$row['quantity']-$amount)
            : (float)$row['quantity']+$amount;

        $status = $newQuantity <= 0 ? 'out_of_stock' : ($newQuantity <= (float)$row['reorder_level'] ? 'low_stock' : 'in_stock');

        $pdo->beginTransaction();
        $update=$pdo->prepare("UPDATE inventory SET quantity=?,status=? WHERE id=? AND branch_id=?");
        $update->execute([$newQuantity,$status,$id,$branchId]);

        if (adminTableExists($pdo,'inventory_movements')) {
            $movement=$pdo->prepare("INSERT INTO inventory_movements(inventory_id,movement_type,quantity,note,created_by) VALUES(?,?,?,?,?)");
            $movement->execute([$id,$type,$amount,$note?:null,(int)$_SESSION['user_id']]);
        }
        $pdo->commit();

        premiumAdminFlash('success','Stock quantity updated.','inventory.php');
    }

    if ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM inventory WHERE id=? AND branch_id=?")->execute([$id,$branchId]);
        premiumAdminFlash('success','Inventory item removed.','inventory.php');
    }
}

$search=trim((string)($_GET['search']??''));
$status=trim((string)($_GET['status']??'all'));
$where=['branch_id=:branch_scope'];$params=['branch_scope'=>$branchId];
if($search!==''){$where[]='item_name LIKE :search';$params['search']='%'.$search.'%';}
if($status!=='all'){$where[]='status=:status';$params['status']=$status;}
$stmt=$pdo->prepare("SELECT * FROM inventory WHERE ".implode(' AND ',$where)." ORDER BY CASE status WHEN 'out_of_stock' THEN 0 WHEN 'low_stock' THEN 1 ELSE 2 END,item_name");
premiumAdminBind($stmt,$params);$stmt->execute();$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>
<link rel="stylesheet" href="../assets/css/premium-admin-suite.css">
<div class="admin-layout"><?php include '../includes/admin-sidebar.php'; ?><main class="admin-content">
<header class="admin-page-header premium-admin-header"><div><p class="premium-admin-kicker">Stock management</p><h1>Inventory</h1><p>Monitor supplies, adjust quantities, and act on low-stock alerts.</p></div><button class="admin-btn admin-btn-primary" data-premium-modal-open="itemModal">＋ Add Item</button></header>
<?php if($flash):?><div class="premium-admin-alert <?=htmlspecialchars($flash['type'])?>"><?=htmlspecialchars($flash['message'])?></div><?php endif;?>
<section class="admin-card premium-admin-toolbar"><div class="premium-admin-toolbar-head"><div><p class="premium-admin-kicker">Inventory controls</p><h2>Search and manage stock</h2><p>Instantly filter the visible items or apply a status filter.</p></div></div><form method="GET" class="premium-admin-filter compact"><div class="admin-field"><label>Search</label><div class="premium-admin-search"><span>⌕</span><input name="search" value="<?=htmlspecialchars($search)?>" data-live-search="inventoryRows" placeholder="Item name"></div></div><div class="admin-field"><label>Stock status</label><select name="status"><?php foreach(['all','in_stock','low_stock','out_of_stock'] as $option):?><option value="<?=$option?>" <?=$status===$option?'selected':''?>><?=premiumAdminStatus($option)?></option><?php endforeach;?></select></div><div class="premium-admin-filter-actions"><button class="admin-btn admin-btn-primary">Apply</button><a class="admin-btn admin-btn-light" href="inventory.php">Reset</a></div></form></section>
<div class="premium-admin-result-line"><strong><?=count($rows)?></strong><span>inventory items</span></div>
<section class="admin-card premium-admin-table-card"><div class="admin-table-wrap"><table class="premium-admin-table"><thead><tr><th>Item</th><th>Quantity</th><th>Reorder level</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead><tbody id="inventoryRows"><?php if(!$rows):?><tr><td colspan="6"><div class="premium-admin-empty"><span>□</span><h2>No inventory items</h2><p>Add the supplies used by the salon.</p></div></td></tr><?php endif;?><?php foreach($rows as $row):?><tr data-search-row="<?=htmlspecialchars(strtolower($row['item_name']))?>"><td class="premium-admin-table-main"><strong><?=htmlspecialchars($row['item_name'])?></strong><small>#<?=(int)$row['id']?></small></td><td><?=number_format((float)$row['quantity'],2)?> <?=htmlspecialchars($row['unit']??'unit')?></td><td><?=number_format((float)$row['reorder_level'],2)?> <?=htmlspecialchars($row['unit']??'unit')?></td><td><span class="premium-admin-badge <?=htmlspecialchars($row['status'])?>"><?=premiumAdminStatus($row['status'])?></span></td><td><?=date('M j, Y',strtotime($row['updated_at']))?></td><td><div class="premium-admin-row-actions"><button class="primary" data-premium-modal-open="itemModal" data-payload="<?=htmlspecialchars(json_encode($row),ENT_QUOTES)?>">Edit</button><button data-premium-modal-open="adjustModal" data-payload="<?=htmlspecialchars(json_encode($row),ENT_QUOTES)?>">Adjust</button><form method="POST" data-premium-confirm="Remove this inventory item?"><input type="hidden" name="csrf_token" value="<?=premiumAdminCsrf()?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=(int)$row['id']?>"><button class="danger">Remove</button></form></div></td></tr><?php endforeach;?></tbody></table></div></section>
<?php include '../includes/admin-footer.php';?></main></div>

<div class="premium-admin-modal" id="itemModal"><div class="premium-admin-modal-backdrop" data-premium-modal-close></div><section class="premium-admin-modal-dialog"><header><div><p class="premium-admin-kicker">Stock item</p><h2>Add or Edit Item</h2></div><button class="premium-admin-modal-close" data-premium-modal-close>×</button></header><form method="POST"><input type="hidden" name="csrf_token" value="<?=premiumAdminCsrf()?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" data-field="id"><div class="premium-admin-form-grid"><div class="admin-field premium-admin-span-2"><label>Item name</label><input name="item_name" data-field="item_name" required></div><div class="admin-field"><label>Quantity</label><input name="quantity" type="number" min="0" step=".01" data-field="quantity" required></div><div class="admin-field"><label>Reorder level</label><input name="reorder_level" type="number" min="0" step=".01" data-field="reorder_level" required></div><div class="admin-field"><label>Unit</label><input name="unit" data-field="unit" placeholder="bottle, box, piece"></div></div><div class="premium-admin-modal-actions"><button type="button" class="admin-btn admin-btn-light" data-premium-modal-close>Cancel</button><button class="admin-btn admin-btn-primary">Save Item</button></div></form></section></div>

<div class="premium-admin-modal" id="adjustModal"><div class="premium-admin-modal-backdrop" data-premium-modal-close></div><section class="premium-admin-modal-dialog"><header><div><p class="premium-admin-kicker">Stock movement</p><h2>Adjust Quantity</h2></div><button class="premium-admin-modal-close" data-premium-modal-close>×</button></header><form method="POST"><input type="hidden" name="csrf_token" value="<?=premiumAdminCsrf()?>"><input type="hidden" name="action" value="adjust"><input type="hidden" name="id" data-field="id"><div class="premium-admin-form-grid"><div class="admin-field"><label>Movement</label><select name="adjustment_type"><option value="in">Stock in</option><option value="out">Stock out</option></select></div><div class="admin-field"><label>Quantity</label><input name="amount" type="number" min=".01" step=".01" required></div><div class="admin-field premium-admin-span-2"><label>Note</label><textarea name="note" rows="3"></textarea></div></div><div class="premium-admin-modal-actions"><button type="button" class="admin-btn admin-btn-light" data-premium-modal-close>Cancel</button><button class="admin-btn admin-btn-primary">Apply Adjustment</button></div></form></section></div>
<script src="../assets/js/premium-admin-suite.js"></script>
