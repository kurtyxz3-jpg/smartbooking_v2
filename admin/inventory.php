<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/premium-admin-helper.php';
require_once '../includes/branch-context.php';

$pageTitle = 'Inventory Management';
$branchId = requireAssignedBranch($pdo);
$csrfToken = premiumAdminCsrf();

$categories = $pdo->query(
    "SELECT id, category_name FROM inventory_categories
     WHERE status = 'active' ORDER BY category_name"
)->fetchAll(PDO::FETCH_ASSOC);

$supplierStatement = $pdo->prepare(
    "SELECT id, supplier_name FROM suppliers
     WHERE status = 'active' AND (branch_id = ? OR branch_id IS NULL)
     ORDER BY supplier_name"
);
$supplierStatement->execute([$branchId]);
$suppliers = $supplierStatement->fetchAll(PDO::FETCH_ASSOC);

$summaryStatement = $pdo->prepare(
    "SELECT COUNT(*) AS total_items,
            SUM(status = 'low_stock') AS low_stock,
            SUM(status = 'out_of_stock') AS out_of_stock,
            COALESCE(SUM(quantity * cost_price), 0) AS stock_value
     FROM inventory WHERE branch_id = ? AND archived_at IS NULL"
);
$summaryStatement->execute([$branchId]);
$summary = $summaryStatement->fetch(PDO::FETCH_ASSOC) ?: [];

$productStatement = $pdo->prepare(
    "SELECT i.*, c.category_name, s.supplier_name
     FROM inventory i
     LEFT JOIN inventory_categories c ON c.id = i.category_id
     LEFT JOIN suppliers s ON s.id = i.supplier_id
     WHERE i.branch_id = ? AND i.archived_at IS NULL
     ORDER BY FIELD(i.status, 'out_of_stock', 'low_stock', 'in_stock'),
              i.item_name"
);
$productStatement->execute([$branchId]);
$products = $productStatement->fetchAll(PDO::FETCH_ASSOC);

$movementStatement = $pdo->prepare(
    "SELECT m.*, i.item_name, u.fullname AS recorded_by
     FROM inventory_movements m
     INNER JOIN inventory i ON i.id = m.inventory_id
     LEFT JOIN users u ON u.id = m.created_by
     WHERE m.branch_id = ?
     ORDER BY m.created_at DESC LIMIT 12"
);
$movementStatement->execute([$branchId]);
$movements = $movementStatement->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>
<link rel="stylesheet" href="../assets/css/inventory-management.css">

<div class="admin-layout">
    <?php include '../includes/admin-sidebar.php'; ?>
    <main class="admin-content inventory-page"
          data-endpoint="ajax/inventory-actions.php"
          data-csrf="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <header class="inventory-hero">
            <div>
                <span class="inventory-eyebrow">Branch stock control</span>
                <h1>Inventory Management</h1>
                <p>Track products, suppliers, stock movements, and reorder alerts for
                    <?= htmlspecialchars(branchName($pdo, $branchId), ENT_QUOTES, 'UTF-8') ?>.</p>
            </div>
            <div class="inventory-primary-actions">
                <button class="inventory-btn secondary" type="button" data-modal-open="categoryModal">Categories</button>
                <button class="inventory-btn secondary" type="button" data-modal-open="supplierModal">Suppliers</button>
                <button class="inventory-btn primary" type="button" data-modal-open="productModal">+ Add Product</button>
            </div>
        </header>

        <section class="inventory-kpis" aria-label="Inventory summary">
            <article><span>Total products</span><strong><?= (int)($summary['total_items'] ?? 0) ?></strong><small>Active catalog items</small></article>
            <article class="warning"><span>Low stock</span><strong><?= (int)($summary['low_stock'] ?? 0) ?></strong><small>At or below reorder level</small></article>
            <article class="danger"><span>Out of stock</span><strong><?= (int)($summary['out_of_stock'] ?? 0) ?></strong><small>Immediate attention needed</small></article>
            <article><span>Estimated stock value</span><strong>₱<?= number_format((float)($summary['stock_value'] ?? 0), 2) ?></strong><small>Quantity × unit cost</small></article>
        </section>

        <section class="inventory-workspace">
            <div class="inventory-panel inventory-products-panel">
                <div class="inventory-panel-heading">
                    <div><span class="inventory-eyebrow">Product catalog</span><h2>Branch Inventory</h2></div>
                    <span id="inventoryResultCount"><?= count($products) ?> products</span>
                </div>

                <div class="inventory-toolbar">
                    <label class="inventory-search">
                        <span>Search</span>
                        <input id="inventorySearch" type="search" placeholder="Name, SKU, barcode, supplier…">
                    </label>
                    <label><span>Category</span>
                        <select id="inventoryCategoryFilter">
                            <option value="">All categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int)$category['id'] ?>"><?= htmlspecialchars($category['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label><span>Stock status</span>
                        <select id="inventoryStatusFilter">
                            <option value="">All statuses</option>
                            <option value="in_stock">In stock</option>
                            <option value="low_stock">Low stock</option>
                            <option value="out_of_stock">Out of stock</option>
                        </select>
                    </label>
                    <button id="inventoryResetFilters" class="inventory-btn ghost" type="button">Reset</button>
                </div>

                <div class="inventory-table-wrap">
                    <table class="inventory-table">
                        <thead><tr><th>Product</th><th>Category</th><th>Stock</th><th>Supplier</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody id="inventoryProductRows">
                        <?php foreach ($products as $product): ?>
                            <?php
                            $searchText = strtolower(implode(' ', [
                                $product['item_name'], $product['sku'] ?? '',
                                $product['barcode'] ?? '', $product['category_name'] ?? '',
                                $product['supplier_name'] ?? ''
                            ]));
                            ?>
                            <tr data-product='<?= htmlspecialchars(json_encode($product), ENT_QUOTES, 'UTF-8') ?>'
                                data-search="<?= htmlspecialchars($searchText, ENT_QUOTES, 'UTF-8') ?>"
                                data-category="<?= (int)($product['category_id'] ?? 0) ?>"
                                data-status="<?= htmlspecialchars($product['status']) ?>">
                                <td><strong><?= htmlspecialchars($product['item_name']) ?></strong>
                                    <small><?= htmlspecialchars($product['sku'] ?: 'No SKU') ?>
                                        <?= $product['barcode'] ? ' · ' . htmlspecialchars($product['barcode']) : '' ?></small></td>
                                <td><?= htmlspecialchars($product['category_name'] ?: 'Uncategorized') ?></td>
                                <td><strong><?= number_format((float)$product['quantity'], 2) ?></strong>
                                    <small><?= htmlspecialchars($product['unit'] ?: 'unit') ?> · reorder at <?= number_format((float)$product['reorder_level'], 2) ?></small></td>
                                <td><?= htmlspecialchars($product['supplier_name'] ?: 'Not assigned') ?></td>
                                <td><span class="inventory-badge <?= htmlspecialchars($product['status']) ?>"><?= ucwords(str_replace('_', ' ', $product['status'])) ?></span></td>
                                <td><div class="inventory-row-actions">
                                    <button type="button" data-action="edit">Edit</button>
                                    <button type="button" data-action="stock">Stock</button>
                                    <button type="button" data-action="history">History</button>
                                    <button type="button" class="danger" data-action="archive">Remove</button>
                                </div></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div id="inventoryEmptyState" class="inventory-empty" <?= $products ? 'hidden' : '' ?>>
                        <strong>No matching products</strong><span>Add a product or change the current filters.</span>
                    </div>
                </div>
            </div>

            <aside class="inventory-panel movement-panel">
                <div class="inventory-panel-heading"><div><span class="inventory-eyebrow">Audit trail</span><h2>Recent Movements</h2></div></div>
                <div id="recentMovementList" class="movement-list">
                    <?php foreach ($movements as $movement): ?>
                        <article><span class="movement-icon <?= htmlspecialchars($movement['movement_type']) ?>"><?= $movement['movement_type'] === 'in' ? '+' : ($movement['movement_type'] === 'out' ? '−' : '↔') ?></span>
                            <div><strong><?= htmlspecialchars($movement['item_name']) ?></strong>
                                <span><?= ucwords(str_replace('_', ' ', $movement['movement_type'])) ?> · <?= number_format((float)$movement['quantity'], 2) ?></span>
                                <small><?= htmlspecialchars($movement['note'] ?: 'No note') ?> · <?= date('M j, g:i A', strtotime($movement['created_at'])) ?></small></div>
                        </article>
                    <?php endforeach; ?>
                    <?php if (!$movements): ?><div class="inventory-empty"><strong>No movements yet</strong><span>Stock changes will appear here.</span></div><?php endif; ?>
                </div>
            </aside>
        </section>

    </main>
</div>

<?php
function inventoryOptions(array $rows, string $valueKey, string $labelKey): void {
    foreach ($rows as $row) {
        echo '<option value="' . (int)$row[$valueKey] . '">' .
            htmlspecialchars((string)$row[$labelKey], ENT_QUOTES, 'UTF-8') . '</option>';
    }
}
?>
<dialog class="inventory-modal" id="productModal"><form id="productForm">
    <header><div><span class="inventory-eyebrow">Product record</span><h2>Add or Edit Product</h2></div><button type="button" data-modal-close>×</button></header>
    <input type="hidden" name="action" value="save_product"><input type="hidden" name="id">
    <div class="inventory-form-grid">
        <label class="span-2">Product name<input name="item_name" required maxlength="150"></label>
        <label>SKU<input name="sku" maxlength="80"></label><label>Barcode (optional)<input name="barcode" maxlength="120"></label>
        <label>Category<select name="category_id"><option value="">Uncategorized</option><?php inventoryOptions($categories, 'id', 'category_name'); ?></select></label>
        <label>Supplier<select name="supplier_id"><option value="">No supplier</option><?php inventoryOptions($suppliers, 'id', 'supplier_name'); ?></select></label>
        <label>Opening quantity<input name="quantity" type="number" min="0" step=".01" required></label>
        <label>Reorder level<input name="reorder_level" type="number" min="0" step=".01" required></label>
        <label>Unit<input name="unit" placeholder="bottle, box, piece" maxlength="50"></label>
        <label>Unit cost<input name="cost_price" type="number" min="0" step=".01"></label>
        <label class="span-2">Notes<textarea name="notes" rows="3" maxlength="500"></textarea></label>
    </div>
    <footer><button type="button" class="inventory-btn ghost" data-modal-close>Cancel</button><button class="inventory-btn primary">Save Product</button></footer>
</form></dialog>

<dialog class="inventory-modal" id="stockModal"><form id="stockForm">
    <header><div><span class="inventory-eyebrow">Stock transaction</span><h2>Stock In / Out</h2></div><button type="button" data-modal-close>×</button></header>
    <input type="hidden" name="action" value="adjust_stock"><input type="hidden" name="id">
    <p class="modal-product-name" data-stock-product></p>
    <div class="inventory-form-grid">
        <label>Movement<select name="movement_type"><option value="in">Stock in</option><option value="out">Stock out</option><option value="adjustment">Set exact quantity</option></select></label>
        <label>Quantity<input name="quantity" type="number" min=".01" step=".01" required></label>
        <label class="span-2">Reference / note<textarea name="note" rows="3" maxlength="255" required></textarea></label>
    </div>
    <footer><button type="button" class="inventory-btn ghost" data-modal-close>Cancel</button><button class="inventory-btn primary">Save Movement</button></footer>
</form></dialog>

<dialog class="inventory-modal small" id="categoryModal"><form id="categoryForm">
    <header><div><span class="inventory-eyebrow">Catalog setup</span><h2>Add Category</h2></div><button type="button" data-modal-close>×</button></header>
    <input type="hidden" name="action" value="save_category">
    <label>Category name<input name="category_name" required maxlength="100"></label>
    <label>Description<textarea name="description" rows="3" maxlength="255"></textarea></label>
    <footer><button type="button" class="inventory-btn ghost" data-modal-close>Cancel</button><button class="inventory-btn primary">Save Category</button></footer>
</form></dialog>

<dialog class="inventory-modal small" id="supplierModal"><form id="supplierForm">
    <header><div><span class="inventory-eyebrow">Supply partner</span><h2>Add Supplier</h2></div><button type="button" data-modal-close>×</button></header>
    <input type="hidden" name="action" value="save_supplier">
    <label>Supplier name<input name="supplier_name" required maxlength="150"></label>
    <div class="inventory-form-grid"><label>Contact person<input name="contact_person" maxlength="120"></label><label>Phone<input name="phone" maxlength="50"></label></div>
    <label>Email<input name="email" type="email" maxlength="150"></label><label>Address<textarea name="address" rows="3"></textarea></label>
    <footer><button type="button" class="inventory-btn ghost" data-modal-close>Cancel</button><button class="inventory-btn primary">Save Supplier</button></footer>
</form></dialog>

<dialog class="inventory-modal" id="historyModal">
    <header><div><span class="inventory-eyebrow">Product audit trail</span><h2>Movement History</h2></div><button type="button" data-modal-close>×</button></header>
    <div id="movementHistory" class="movement-history"></div>
</dialog>

<div class="inventory-toast" id="inventoryToast" role="status" aria-live="polite"></div>
<script src="../assets/js/inventory-management.js"></script>
<?php include '../includes/admin-footer.php'; ?>
