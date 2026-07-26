<?php
declare(strict_types=1);
$pageTitle = 'My Beauty Previews';
$customerPage = 'ai-beauty-history';
require_once '../includes/customer-header.php';
$statement = $pdo->prepare(
    "SELECT x.id, x.image_path, x.is_selected, x.created_at,
            r.beauty_area, r.color_value, r.service_style, r.finish_type,
            r.brow_shape, r.thickness
     FROM ai_beauty_results x
     INNER JOIN ai_beauty_requests r ON r.id=x.request_id
     WHERE x.customer_id=? AND x.is_saved=1 AND x.deleted_at IS NULL
     ORDER BY x.is_selected DESC, x.created_at DESC"
);
$statement->execute([(int) $_SESSION['user_id']]);
$previews = $statement->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="../assets/css/ai-design-history.css">
<main class="customer-page-shell ai-history-shell">
    <header class="customer-page-heading">
        <div><p class="customer-kicker">AI Beauty Studio</p>
        <h1>My Beauty Previews</h1>
        <p>Saved lip and eyebrow service directions.</p></div>
        <a class="customer-ui-button primary" href="beauty-studio.php">Create a preview</a>
    </header>
    <?php if (!$previews): ?>
        <section class="customer-ui-panel ai-history-empty">
            <h2>No saved beauty previews yet</h2>
            <p>Create and select a lip or eyebrow preview to save it here.</p>
        </section>
    <?php else: ?>
        <section class="ai-history-grid">
        <?php foreach ($previews as $preview): ?>
            <article class="customer-ui-panel ai-history-card">
                <a class="ai-history-preview" href="<?= htmlspecialchars($preview['image_path']) ?>" target="_blank">
                    <img src="<?= htmlspecialchars($preview['image_path']) ?>"
                         alt="<?= htmlspecialchars(ucfirst($preview['beauty_area'])) ?> preview">
                </a>
                <div class="ai-history-content">
                    <div class="ai-history-title">
                        <h2><?= htmlspecialchars(ucfirst($preview['beauty_area'])) ?></h2>
                        <?php if ((int) $preview['is_selected']): ?><span>Selected</span><?php endif; ?>
                    </div>
                    <p><?= htmlspecialchars($preview['color_value']) ?> ·
                    <?= htmlspecialchars(
                        $preview['beauty_area'] === 'lips'
                            ? ($preview['finish_type'] ?: 'Preview')
                            : (($preview['brow_shape'] ?: 'Natural') . ' · ' . ($preview['thickness'] ?: 'Medium'))
                    ) ?></p>
                    <small><?= date('M d, Y g:i A', strtotime($preview['created_at'])) ?></small>
                    <div class="ai-history-actions">
                        <a class="customer-ui-button primary"
                           href="book-beauty-preview.php?result_id=<?= (int) $preview['id'] ?>">Book this service</a>
                        <a class="customer-ui-button secondary"
                           href="<?= htmlspecialchars($preview['image_path']) ?>" download>Download</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
<?php include '../includes/customer-footer.php'; ?>

