<?php
declare(strict_types=1);

$pageTitle = 'My AI Designs';
$customerPage = 'ai-designs';
require_once '../includes/customer-header.php';
require_once '../includes/ai-history-helper.php';

if (empty($_SESSION['ai_history_csrf'])) {
    $_SESSION['ai_history_csrf'] = bin2hex(random_bytes(32));
}
$customerId = (int) $_SESSION['user_id'];
$deletedClause = aiTableHasColumn($pdo, 'ai_generated_designs', 'deleted_at')
    ? ' AND d.deleted_at IS NULL' : '';
$statement = $pdo->prepare(
    "SELECT d.id, d.request_id, d.design_number, d.image_path, d.is_selected,
            d.created_at, r.nail_shape, r.nail_length, r.colors,
            r.design_style, r.occasion, r.add_ons, r.instructions
     FROM ai_generated_designs d
     INNER JOIN ai_design_requests r ON r.id=d.request_id
     WHERE d.customer_id=? AND d.is_saved=1 {$deletedClause}
     ORDER BY d.is_selected DESC, d.created_at DESC"
);
$statement->execute([$customerId]);
$designs = $statement->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="../assets/css/ai-design-history.css">

<main class="customer-page-shell ai-history-shell">
    <header class="customer-page-heading">
        <div>
            <p class="customer-kicker">AI Nail Studio</p>
            <h1>My AI Designs</h1>
            <p>Review saved concepts, reuse their preferences, or attach one to an appointment.</p>
        </div>
        <a class="customer-ui-button primary" href="ai-generator.php">Create new designs</a>
    </header>

    <div id="aiHistoryMessage" class="ai-history-message" hidden></div>
    <?php if (!$designs): ?>
        <section class="customer-ui-panel ai-history-empty">
            <h2>No saved designs yet</h2>
            <p>Generate three nail concepts and select your favorite to save it here.</p>
            <a class="customer-ui-button primary" href="ai-generator.php">Open AI Nail Studio</a>
        </section>
    <?php else: ?>
        <section class="ai-history-grid">
            <?php foreach ($designs as $design): ?>
                <article class="customer-ui-panel ai-history-card" data-design-id="<?= (int) $design['id'] ?>">
                    <button class="ai-history-preview" type="button"
                            data-image="<?= htmlspecialchars($design['image_path']) ?>">
                        <img src="<?= htmlspecialchars($design['image_path']) ?>"
                             alt="<?= htmlspecialchars($design['nail_shape']) ?> nail design">
                    </button>
                    <div class="ai-history-content">
                        <div class="ai-history-title">
                            <h2><?= htmlspecialchars($design['design_style']) ?></h2>
                            <?php if ((int) $design['is_selected'] === 1): ?>
                                <span>Selected</span>
                            <?php endif; ?>
                        </div>
                        <p><?= htmlspecialchars($design['nail_shape']) ?> ·
                           <?= htmlspecialchars($design['nail_length']) ?> ·
                           <?= htmlspecialchars($design['colors']) ?></p>
                        <small><?= date('M d, Y g:i A', strtotime($design['created_at'])) ?></small>
                        <div class="ai-history-actions">
                            <a class="customer-ui-button primary"
                               href="book-ai-design.php?design_id=<?= (int) $design['id'] ?>">Book this design</a>
                            <a class="customer-ui-button secondary"
                               href="ai-generator.php?similar_to=<?= (int) $design['id'] ?>">Generate similar</a>
                            <a class="customer-ui-button secondary"
                               href="<?= htmlspecialchars($design['image_path']) ?>" download>Download</a>
                            <button class="customer-ui-button secondary ai-history-delete"
                                    type="button">Delete</button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>

<div id="aiHistoryModal" class="ai-history-modal" hidden>
    <button class="ai-history-backdrop" type="button" aria-label="Close preview"></button>
    <div class="ai-history-modal-panel">
        <button class="ai-history-modal-close" type="button" aria-label="Close">&times;</button>
        <img src="" alt="Saved AI nail design">
    </div>
</div>
<script>
window.SMARTBOOKING_AI_HISTORY = {
    csrfToken: <?= json_encode($_SESSION['ai_history_csrf']) ?>,
    deleteEndpoint: 'api/delete-ai-design.php'
};
</script>
<script src="../assets/js/ai-design-history.js"></script>
<?php include '../includes/customer-footer.php'; ?>

