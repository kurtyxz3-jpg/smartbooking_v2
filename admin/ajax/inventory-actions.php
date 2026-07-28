<?php
declare(strict_types=1);

require_once '../../includes/admin-auth.php';
require_once '../../config/database.php';
require_once '../../includes/premium-admin-helper.php';
require_once '../../includes/branch-context.php';

header('Content-Type: application/json; charset=utf-8');

function respond(bool $success, string $message, array $data = [], int $status = 200): never
{
    http_response_code($status);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

try {
    $branchId = requireAssignedBranch($pdo);
    $payload = $_POST;
    if (str_contains((string)($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json')) {
        $payload = json_decode((string)file_get_contents('php://input'), true) ?: [];
    }

    if (!premiumAdminVerifyCsrf($payload['csrf_token'] ?? null)) {
        respond(false, 'Your session token expired. Refresh the page and try again.', [], 419);
    }

    $action = (string)($payload['action'] ?? '');
    $userId = (int)($_SESSION['user_id'] ?? 0);

    if ($action === 'save_product') {
        $id = filter_var($payload['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
        $name = trim((string)($payload['item_name'] ?? ''));
        $quantity = max(0, (float)($payload['quantity'] ?? 0));
        $reorder = max(0, (float)($payload['reorder_level'] ?? 0));
        $cost = max(0, (float)($payload['cost_price'] ?? 0));
        if ($name === '') respond(false, 'Product name is required.', [], 422);

        $categoryId = filter_var($payload['category_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
        $supplierId = filter_var($payload['supplier_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
        $status = $quantity <= 0 ? 'out_of_stock' : ($quantity <= $reorder ? 'low_stock' : 'in_stock');
        $values = [
            $name, trim((string)($payload['sku'] ?? '')) ?: null,
            trim((string)($payload['barcode'] ?? '')) ?: null,
            $categoryId, $supplierId, $quantity, $reorder,
            trim((string)($payload['unit'] ?? '')) ?: 'unit', $cost,
            trim((string)($payload['notes'] ?? '')) ?: null, $status
        ];

        if ($id) {
            $statement = $pdo->prepare(
                "UPDATE inventory SET item_name=?, sku=?, barcode=?, category_id=?,
                 supplier_id=?, quantity=?, reorder_level=?, unit=?, cost_price=?,
                 notes=?, status=? WHERE id=? AND branch_id=? AND archived_at IS NULL"
            );
            $statement->execute([...$values, $id, $branchId]);
            if (!$statement->rowCount()) respond(false, 'Product was not found in your branch.', [], 404);
        } else {
            $statement = $pdo->prepare(
                "INSERT INTO inventory
                 (item_name, sku, barcode, category_id, supplier_id, quantity,
                  reorder_level, unit, cost_price, notes, status, branch_id)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?)"
            );
            $statement->execute([...$values, $branchId]);
            $id = (int)$pdo->lastInsertId();
            if ($quantity > 0) {
                $movement = $pdo->prepare(
                    "INSERT INTO inventory_movements
                     (inventory_id, branch_id, movement_type, quantity,
                      quantity_before, quantity_after, note, created_by)
                     VALUES (?,?,?,?,?,?,?,?)"
                );
                $movement->execute([$id, $branchId, 'in', $quantity, 0, $quantity, 'Opening stock', $userId]);
            }
        }
        respond(true, 'Product saved successfully.', ['id' => $id]);
    }

    if ($action === 'adjust_stock') {
        $id = filter_var($payload['id'] ?? null, FILTER_VALIDATE_INT);
        $type = (string)($payload['movement_type'] ?? '');
        $amount = (float)($payload['quantity'] ?? 0);
        $note = trim((string)($payload['note'] ?? ''));
        if (!$id || !in_array($type, ['in', 'out', 'adjustment'], true) || $amount < 0 || $note === '') {
            respond(false, 'Complete the stock movement details.', [], 422);
        }

        $pdo->beginTransaction();
        $statement = $pdo->prepare(
            "SELECT id, quantity, reorder_level FROM inventory
             WHERE id=? AND branch_id=? AND archived_at IS NULL FOR UPDATE"
        );
        $statement->execute([$id, $branchId]);
        $product = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            $pdo->rollBack();
            respond(false, 'Product was not found in your branch.', [], 404);
        }

        $before = (float)$product['quantity'];
        $after = $type === 'in' ? $before + $amount
            : ($type === 'out' ? $before - $amount : $amount);
        if ($after < 0) {
            $pdo->rollBack();
            respond(false, 'Stock out cannot exceed the available quantity.', [], 422);
        }
        $status = $after <= 0 ? 'out_of_stock'
            : ($after <= (float)$product['reorder_level'] ? 'low_stock' : 'in_stock');

        $pdo->prepare("UPDATE inventory SET quantity=?, status=? WHERE id=? AND branch_id=?")
            ->execute([$after, $status, $id, $branchId]);
        $pdo->prepare(
            "INSERT INTO inventory_movements
             (inventory_id, branch_id, movement_type, quantity,
              quantity_before, quantity_after, note, created_by)
             VALUES (?,?,?,?,?,?,?,?)"
        )->execute([$id, $branchId, $type, $amount, $before, $after, $note, $userId]);
        $pdo->commit();
        respond(true, 'Stock movement recorded.', ['quantity' => $after, 'status' => $status]);
    }

    if ($action === 'archive_product') {
        $id = filter_var($payload['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) respond(false, 'Invalid product.', [], 422);
        $statement = $pdo->prepare(
            "UPDATE inventory SET archived_at=NOW() WHERE id=? AND branch_id=?"
        );
        $statement->execute([$id, $branchId]);
        respond(true, 'Product removed from the active inventory.');
    }

    if ($action === 'save_category') {
        $name = trim((string)($payload['category_name'] ?? ''));
        if ($name === '') respond(false, 'Category name is required.', [], 422);
        $pdo->prepare(
            "INSERT INTO inventory_categories(category_name, description)
             VALUES (?,?) ON DUPLICATE KEY UPDATE description=VALUES(description), status='active'"
        )->execute([$name, trim((string)($payload['description'] ?? '')) ?: null]);
        respond(true, 'Category saved.');
    }

    if ($action === 'save_supplier') {
        $name = trim((string)($payload['supplier_name'] ?? ''));
        if ($name === '') respond(false, 'Supplier name is required.', [], 422);
        $pdo->prepare(
            "INSERT INTO suppliers
             (branch_id, supplier_name, contact_person, phone, email, address)
             VALUES (?,?,?,?,?,?)"
        )->execute([
            $branchId, $name, trim((string)($payload['contact_person'] ?? '')) ?: null,
            trim((string)($payload['phone'] ?? '')) ?: null,
            trim((string)($payload['email'] ?? '')) ?: null,
            trim((string)($payload['address'] ?? '')) ?: null
        ]);
        respond(true, 'Supplier saved.');
    }

    if ($action === 'movement_history') {
        $id = filter_var($payload['id'] ?? null, FILTER_VALIDATE_INT);
        $statement = $pdo->prepare(
            "SELECT m.*, u.fullname AS recorded_by
             FROM inventory_movements m
             LEFT JOIN users u ON u.id=m.created_by
             WHERE m.inventory_id=? AND m.branch_id=?
             ORDER BY m.created_at DESC LIMIT 100"
        );
        $statement->execute([$id, $branchId]);
        respond(true, 'Movement history loaded.', ['movements' => $statement->fetchAll(PDO::FETCH_ASSOC)]);
    }

    respond(false, 'Unsupported inventory action.', [], 400);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Inventory AJAX error: ' . $exception->getMessage());
    respond(false, 'The inventory action could not be completed.', [], 500);
}
