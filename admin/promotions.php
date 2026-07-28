<?php
declare(strict_types=1);

require_once '../includes/admin-auth.php';
require_once '../config/database.php';
require_once '../includes/admin-helper.php';
require_once '../includes/premium-admin-helper.php';
require_once '../includes/branch-context.php';

$pageTitle = 'Promotions';
$flash = premiumAdminGetFlash();
$branchId = requireAssignedBranch($pdo);

$pdo->prepare(
    "UPDATE promotions
     SET status = 'expired'
     WHERE branch_id = ?
       AND end_date IS NOT NULL
       AND end_date < CURDATE()
       AND status <> 'expired'"
)->execute([$branchId]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!premiumAdminVerifyCsrf($_POST['csrf_token'] ?? null)) {
        premiumAdminFlash('error', 'Session token expired.', 'promotions.php');
    }

    $action = (string) ($_POST['action'] ?? '');
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($action === 'save') {
        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $badge = trim((string) ($_POST['badge'] ?? 'Limited-time offer'));
        $discount = min(100, max(0, (float) ($_POST['discount_value'] ?? 0)));
        $originalPrice = $_POST['original_price'] !== '' ? max(0, (float) $_POST['original_price']) : null;
        $promoPrice = $originalPrice !== null
            ? round($originalPrice * (1 - min($discount, 100) / 100), 2)
            : null;
        $start = ($_POST['start_date'] ?? '') ?: null;
        $end = ($_POST['end_date'] ?? '') ?: null;
        $status = in_array($_POST['status'] ?? '', ['active', 'inactive', 'expired'], true)
            ? $_POST['status']
            : 'inactive';
        $buttonLabel = trim((string) ($_POST['button_label'] ?? 'View offer'));
        $buttonLink = trim((string) ($_POST['button_link'] ?? 'customer/services.php'));
        $imagePath = ($_POST['remove_image'] ?? '') === '1'
            ? ''
            : trim((string) ($_POST['image_path'] ?? ''));

        if (
            isset($_FILES['promotion_image'])
            && (int) $_FILES['promotion_image']['error'] !== UPLOAD_ERR_NO_FILE
        ) {
            $upload = $_FILES['promotion_image'];

            if ((int) $upload['error'] !== UPLOAD_ERR_OK) {
                premiumAdminFlash('error', 'The promotion photo could not be uploaded.', 'promotions.php');
            }

            if ((int) $upload['size'] > 5 * 1024 * 1024) {
                premiumAdminFlash('error', 'Promotion photos must be 5 MB or smaller.', 'promotions.php');
            }

            $mimeDetector = new finfo(FILEINFO_MIME_TYPE);
            $mime = $mimeDetector->file((string) $upload['tmp_name']);
            $allowedImages = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ];

            if (!isset($allowedImages[$mime])) {
                premiumAdminFlash('error', 'Choose a JPG, PNG, or WebP photo.', 'promotions.php');
            }

            $uploadDirectory = __DIR__ . '/../uploads/promotions';
            if (
                !is_dir($uploadDirectory)
                && !mkdir($uploadDirectory, 0775, true)
                && !is_dir($uploadDirectory)
            ) {
                premiumAdminFlash('error', 'The promotion photo folder could not be created.', 'promotions.php');
            }

            $fileName = 'promotion-'
                . bin2hex(random_bytes(10))
                . '.'
                . $allowedImages[$mime];
            $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $fileName;

            if (!move_uploaded_file((string) $upload['tmp_name'], $destination)) {
                premiumAdminFlash('error', 'The promotion photo could not be saved.', 'promotions.php');
            }

            $imagePath = '/smartbooking_v2/uploads/promotions/' . $fileName;
        }
        $theme = in_array($_POST['theme'] ?? '', ['gold', 'rose', 'brown'], true)
            ? $_POST['theme']
            : 'gold';
        $showPopup = isset($_POST['show_in_popup']) ? 1 : 0;
        $showHomepage = isset($_POST['show_on_homepage']) ? 1 : 0;
        $isGlobal = isset($_POST['is_global']) ? 1 : 0;
        $displayOrder = max(0, (int) ($_POST['display_order'] ?? 0));

        if ($title === '' || ($start && $end && $start > $end)) {
            premiumAdminFlash('error', 'Enter valid promotion details.', 'promotions.php');
        }

        $values = [
            $title,
            $description ?: null,
            $badge ?: 'Limited-time offer',
            $discount,
            $promoPrice,
            $originalPrice,
            $start,
            $end,
            $status,
            $buttonLabel ?: 'View offer',
            $buttonLink ?: 'customer/services.php',
            $imagePath ?: null,
            $theme,
            $showPopup,
            $showHomepage,
            $isGlobal,
            $displayOrder,
        ];

        if ($id) {
            $values[] = $id;
            $values[] = $branchId;
            $statement = $pdo->prepare(
                "UPDATE promotions
                 SET title=?, description=?, badge=?, discount_value=?,
                     promo_price=?, original_price=?, start_date=?, end_date=?,
                     status=?, button_label=?, button_link=?, image_path=?,
                     theme=?, show_in_popup=?, show_on_homepage=?, is_global=?,
                     display_order=?
                 WHERE id=? AND branch_id=?"
            );
        } else {
            $values[] = $branchId;
            $statement = $pdo->prepare(
                "INSERT INTO promotions (
                    title, description, badge, discount_value, promo_price,
                    original_price, start_date, end_date, status, button_label,
                    button_link, image_path, theme, show_in_popup,
                    show_on_homepage, is_global, display_order, branch_id
                 ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
            );
        }
        $statement->execute($values);
        premiumAdminFlash('success', 'Promotion saved and popup settings updated.', 'promotions.php');
    }

    if ($action === 'toggle' && $id) {
        $pdo->prepare(
            "UPDATE promotions
             SET status = CASE WHEN status='active' THEN 'inactive' ELSE 'active' END
             WHERE id=? AND branch_id=?"
        )->execute([$id, $branchId]);
        premiumAdminFlash('success', 'Promotion status updated.', 'promotions.php');
    }

    if ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM promotions WHERE id=? AND branch_id=?")
            ->execute([$id, $branchId]);
        premiumAdminFlash('success', 'Promotion removed.', 'promotions.php');
    }
}

$search = trim((string) ($_GET['search'] ?? ''));
$status = trim((string) ($_GET['status'] ?? 'all'));
$where = ['branch_id = :branch_scope'];
$params = ['branch_scope' => $branchId];

if ($search !== '') {
    $where[] = '(title LIKE :title_search OR description LIKE :description_search)';
    $params['title_search'] = '%' . $search . '%';
    $params['description_search'] = '%' . $search . '%';
}
if ($status !== 'all') {
    $where[] = 'status = :status';
    $params['status'] = $status;
}

$statement = $pdo->prepare(
    "SELECT *
     FROM promotions
     WHERE " . implode(' AND ', $where) . "
     ORDER BY display_order ASC, start_date DESC, id DESC"
);
premiumAdminBind($statement, $params);
$statement->execute();
$rows = $statement->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>
<link rel="stylesheet" href="../assets/css/premium-admin-suite.css">
<link rel="stylesheet" href="../assets/css/admin-promotions-popup.css?v=083">

<div class="admin-layout">
<?php include '../includes/admin-sidebar.php'; ?>
<main class="admin-content">
    <header class="admin-page-header premium-admin-header">
        <div>
            <p class="premium-admin-kicker">Campaign management</p>
            <h1>Promotions</h1>
            <p>Create, schedule, order, and publish customer offers.</p>
        </div>
        <button class="admin-btn admin-btn-primary" data-premium-modal-open="promotionModal">
            + Add Promotion
        </button>
    </header>

    <?php if ($flash): ?>
        <div class="premium-admin-alert <?= htmlspecialchars($flash['type']) ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <section class="admin-card premium-admin-toolbar">
        <form class="premium-admin-filter compact">
            <div class="admin-field">
                <label for="promotionSearch">Search</label>
                <div class="premium-admin-search">
                    <span aria-hidden="true">⌕</span>
                    <input id="promotionSearch" name="search"
                           value="<?= htmlspecialchars($search) ?>"
                           data-live-search="promotionCards"
                           placeholder="Promotion title or description">
                </div>
            </div>
            <div class="admin-field">
                <label for="promotionStatus">Status</label>
                <select id="promotionStatus" name="status">
                    <?php foreach (['all','active','inactive','expired'] as $option): ?>
                        <option value="<?= $option ?>" <?= $status === $option ? 'selected' : '' ?>>
                            <?= premiumAdminStatus($option) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="premium-admin-filter-actions">
                <button class="admin-btn admin-btn-primary">Apply</button>
                <a class="admin-btn admin-btn-light" href="promotions.php">Reset</a>
            </div>
        </form>
    </section>

    <div class="premium-admin-result-line">
        <strong><?= count($rows) ?></strong><span>promotions</span>
    </div>

    <section class="premium-admin-cards" id="promotionCards">
        <?php if (!$rows): ?>
            <div class="admin-card premium-admin-empty">
                <span>%</span><h2>No promotions</h2>
                <p>Create a scheduled offer for the customer popup.</p>
            </div>
        <?php endif; ?>

        <?php foreach ($rows as $row): ?>
            <article class="premium-admin-card promotion-admin-card"
                     data-search-row="<?= htmlspecialchars(strtolower($row['title'] . ' ' . ($row['description'] ?? ''))) ?>">
                <div class="promotion-admin-image"
                     style="<?= $row['image_path']
                        ? "--promotion-card-image:url('" . htmlspecialchars($row['image_path'], ENT_QUOTES) . "')"
                        : '' ?>">
                    <span><?= (int) $row['display_order'] ?></span>
                </div>
                <div class="premium-admin-card-body">
                    <div class="promotion-admin-badges">
                        <span class="premium-admin-badge <?= htmlspecialchars($row['status']) ?>">
                            <?= premiumAdminStatus($row['status']) ?>
                        </span>
                        <?php if ((int) $row['show_in_popup'] === 1): ?>
                            <span class="promotion-channel-badge">Popup</span>
                        <?php endif; ?>
                        <span class="promotion-scope-badge">
                            <?= (int) $row['is_global'] === 1 ? 'All branches' : 'This branch' ?>
                        </span>
                    </div>
                    <h2><?= htmlspecialchars($row['title']) ?></h2>
                    <p><?= htmlspecialchars($row['description'] ?? 'No campaign description.') ?></p>
                    <div class="premium-admin-card-meta">
                        <div><small>Offer price</small><strong>
                            <?= $row['promo_price'] !== null ? '₱' . number_format((float) $row['promo_price'], 0) : '—' ?>
                        </strong></div>
                        <div><small>Schedule</small><strong>
                            <?= htmlspecialchars(($row['start_date'] ?: 'Open') . ' – ' . ($row['end_date'] ?: 'Open')) ?>
                        </strong></div>
                    </div>
                    <div class="premium-admin-row-actions promotion-admin-actions">
                        <button class="primary" data-premium-modal-open="promotionModal"
                                data-payload="<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>">Edit</button>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= premiumAdminCsrf() ?>">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                            <button><?= $row['status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
                        </form>
                        <form method="POST" data-premium-confirm="Remove this promotion?">
                            <input type="hidden" name="csrf_token" value="<?= premiumAdminCsrf() ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                            <button class="danger">Remove</button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <?php include '../includes/admin-footer.php'; ?>
</main>
</div>

<div class="premium-admin-modal" id="promotionModal">
    <div class="premium-admin-modal-backdrop" data-premium-modal-close></div>
    <section class="premium-admin-modal-dialog promotion-editor-dialog">
        <header>
            <div><p class="premium-admin-kicker">Campaign editor</p><h2>Add or Edit Promotion</h2></div>
            <button class="premium-admin-modal-close" data-premium-modal-close aria-label="Close">×</button>
        </header>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= premiumAdminCsrf() ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" data-field="id">
            <div class="premium-admin-form-grid">
                <div class="admin-field premium-admin-span-2"><label>Title</label><input name="title" data-field="title" required></div>
                <div class="admin-field"><label>Badge</label><input name="badge" data-field="badge" placeholder="Limited-time offer"></div>
                <div class="admin-field"><label>Theme</label><select name="theme" data-field="theme"><option value="gold">Gold</option><option value="rose">Rose</option><option value="brown">Brown</option></select></div>
                <div class="admin-field premium-admin-span-2"><label>Description</label><textarea name="description" rows="3" data-field="description"></textarea></div>
                <div class="admin-field"><label>Discount percentage</label><input name="discount_value" type="number" min="0" max="100" step=".01" data-field="discount_value" data-promotion-discount></div>
                <div class="admin-field"><label>Status</label><select name="status" data-field="status"><option value="active">Active</option><option value="inactive">Inactive</option><option value="expired">Expired</option></select></div>
                <div class="admin-field"><label>Offer price <small>Automatically calculated</small></label><input name="promo_price" type="number" min="0" step=".01" data-field="promo_price" data-promotion-price readonly></div>
                <div class="admin-field"><label>Original price</label><input name="original_price" type="number" min="0" step=".01" data-field="original_price" data-promotion-original-price></div>
                <div class="admin-field"><label>Start date</label><input name="start_date" type="date" data-field="start_date"></div>
                <div class="admin-field"><label>End date</label><input name="end_date" type="date" data-field="end_date"></div>
                <div class="admin-field"><label>Button label</label><input name="button_label" data-field="button_label" placeholder="Book this offer"></div>
                <div class="admin-field"><label>Button link</label><input name="button_link" data-field="button_link" placeholder="customer/book.php"></div>
                <div class="admin-field premium-admin-span-2">
                    <label>Promotion photo</label>
                    <input type="hidden" name="image_path" data-field="image_path">
                    <input type="hidden" name="remove_image" value="" data-promotion-remove-image>
                    <div class="promotion-image-upload" data-promotion-upload>
                        <input
                            class="promotion-image-file"
                            type="file"
                            name="promotion_image"
                            accept="image/jpeg,image/png,image/webp"
                            data-promotion-image-input
                        >
                        <div class="promotion-image-preview" data-promotion-image-preview>
                            <img src="" alt="Promotion preview" hidden>
                            <div data-promotion-image-empty>
                                <strong>Drag and drop a photo here</strong>
                                <span>JPG, PNG, or WebP · maximum 5 MB</span>
                            </div>
                        </div>
                        <div class="promotion-image-actions">
                            <button type="button" class="admin-btn admin-btn-light" data-promotion-choose-image>
                                Choose photo
                            </button>
                            <button type="button" class="admin-btn admin-btn-light" data-promotion-clear-image hidden>
                                Remove photo
                            </button>
                        </div>
                    </div>
                </div>
                <div class="admin-field">
                    <label>Display priority</label>
                    <input name="display_order" type="number" min="0" data-field="display_order" value="0">
                    <small class="promotion-field-help">Lower numbers appear first. This is not the popup timer.</small>
                </div>
                <div class="promotion-editor-options">
                    <label><input type="checkbox" name="show_in_popup" value="1" data-field="show_in_popup"> Show in popup</label>
                    <label><input type="checkbox" name="show_on_homepage" value="1" data-field="show_on_homepage"> Show on homepage</label>
                    <label><input type="checkbox" name="is_global" value="1" data-field="is_global"> All branches</label>
                </div>
            </div>
            <p class="promotion-editor-note">Branch-specific promotions use your assigned branch. “All branches” makes the offer visible business-wide.</p>
            <div class="premium-admin-modal-actions">
                <button type="button" class="admin-btn admin-btn-light" data-premium-modal-close>Cancel</button>
                <button class="admin-btn admin-btn-primary">Save Promotion</button>
            </div>
        </form>
    </section>
</div>

<script src="../assets/js/premium-admin-suite.js"></script>
<script src="../assets/js/admin-promotions-popup.js?v=079c"></script>
