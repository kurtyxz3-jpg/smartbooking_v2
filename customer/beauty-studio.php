<?php
declare(strict_types=1);

$area = strtolower((string) ($_GET['area'] ?? 'nails'));
if (!in_array($area, ['nails', 'lips', 'eyebrows'], true)) {
    $area = 'nails';
}

/*
 * Keep the established Nail Studio implementation intact while presenting it
 * through the unified Beauty Studio URL and navigation entry.
 */
if ($area === 'nails') {
    require __DIR__ . '/ai-generator.php';
    exit;
}

$pageTitle = 'AI Beauty Studio';
$customerPage = 'ai-beauty';
require_once '../includes/customer-header.php';
require_once '../includes/ai-service.php';

if (empty($_SESSION['beauty_ai_csrf'])) {
    $_SESSION['beauty_ai_csrf'] = bin2hex(random_bytes(32));
}
$settings = aiSettings($pdo);
$enabledKey = $area === 'lips' ? 'lip_preview_enabled' : 'eyebrow_preview_enabled';
$available = $settings['ai_enabled'] === '1'
    && ($settings[$enabledKey] ?? '1') === '1';
?>
<link rel="stylesheet" href="../assets/css/ai-beauty-studio.css">
<link rel="stylesheet" href="../assets/css/ai-beauty-navigation.css">
<link rel="stylesheet" href="../assets/css/ai-studio-tabs.css">
<main class="customer-page-shell beauty-studio-shell">
    <header class="customer-page-heading">
        <div>
            <p class="customer-kicker">Phase 2 · AI Beauty Virtual Preview</p>
            <h1><?= $area === 'lips' ? 'Lip Preview Studio' : 'Eyebrow Preview Studio' ?></h1>
            <p>Upload a centered photo or use the demonstration face to explore service directions.</p>
        </div>
        <div class="beauty-heading-actions">
            <a class="beauty-saved-link" href="beauty-previews.php">Saved previews</a>
            <span id="beautyHealth" class="phase2-health">Checking AI service</span>
        </div>
    </header>

    <nav class="ai-studio-tabs" aria-label="Beauty preview type">
        <a href="?area=nails">Nails</a>
        <a class="<?= $area === 'lips' ? 'active' : '' ?>" href="?area=lips">Lips</a>
        <a class="<?= $area === 'eyebrows' ? 'active' : '' ?>" href="?area=eyebrows">Eyebrows</a>
    </nav>

    <?php if (!$available): ?>
        <section class="customer-ui-panel beauty-unavailable">
            <h2>This preview is currently disabled.</h2>
            <p>A Super Administrator can enable it from AI Settings.</p>
        </section>
    <?php else: ?>
        <section class="beauty-layout">
            <form id="beautyStudioForm" class="customer-ui-panel beauty-form">
                <input type="hidden" name="area" value="<?= htmlspecialchars($area) ?>">
                <h2>Photo and preferences</h2>
                <div class="beauty-photo-grid">
                    <label class="beauty-upload">
                        <span>Upload or take a photo</span>
                        <input id="beautyPhoto" type="file" accept="image/jpeg,image/png,image/webp"
                               capture="user">
                        <small>Front-facing, centered, evenly lit; maximum processed size is 1024px.</small>
                    </label>
                    <div class="beauty-photo-preview">
                        <img id="beautyPhotoPreview"
                             src="../assets/images/ai/demo-beauty-face.png"
                             alt="Current beauty preview source">
                        <button id="beautyUseDemo" type="button">Use demonstration face</button>
                    </div>
                </div>
                <div class="beauty-fields">
                    <label>Preferred color
                        <div class="beauty-color-control">
                            <input id="beautyColorPicker" type="color"
                                   value="<?= $area === 'lips' ? '#A71930' : '#4B3028' ?>">
                            <input id="beautyColorText" name="color" required
                                   value="<?= $area === 'lips' ? '#A71930' : '#4B3028' ?>"
                                   placeholder="Color name or HEX">
                        </div>
                    </label>
                    <?php if ($area === 'lips'): ?>
                        <label>Finish
                            <select name="finish">
                                <option>Matte</option><option selected>Satin</option>
                                <option>Gloss</option><option>Tint</option><option>Ombre</option>
                            </select>
                        </label>
                        <label>Service direction
                            <select name="service_style">
                                <option>Everyday color</option><option>Soft glam</option>
                                <option>Bridal</option><option>Bold statement</option>
                            </select>
                        </label>
                    <?php else: ?>
                        <label>Eyebrow shape
                            <select name="brow_shape">
                                <option>Natural</option><option selected>Soft arch</option>
                                <option>High arch</option><option>Straight</option><option>Rounded</option>
                            </select>
                        </label>
                        <label>Thickness
                            <select name="thickness">
                                <option>Thin</option><option selected>Medium</option><option>Full</option>
                            </select>
                        </label>
                    <?php endif; ?>
                </div>
                <label>Special instructions
                    <textarea name="instructions" maxlength="600"
                              placeholder="Optional details for this preview"></textarea>
                </label>
                <label class="beauty-consent">
                    <input name="consent" type="checkbox" required>
                    <span>I consent to processing this photo for a virtual preview. I understand
                    that it is a simulation and may not match the final salon result.</span>
                </label>
                <div id="beautyMessage" class="phase2-message" hidden></div>
                <button id="beautyGenerateButton" class="customer-ui-button primary" type="submit">
                    Generate 3 previews
                </button>
            </form>
            <aside class="customer-ui-panel beauty-guidance">
                <p class="customer-kicker">Photo guidance</p>
                <h2>For the clearest demo</h2>
                <ul>
                    <li>Face the camera directly.</li>
                    <li>Use bright, even lighting.</li>
                    <li>Keep lips closed and eyebrows visible.</li>
                    <li>Avoid filters, glasses, and heavy makeup.</li>
                </ul>
                <p class="beauty-disclaimer"><strong>Important:</strong> Demo mode uses calibrated
                overlays. Accurate arbitrary-photo alignment requires facial landmark detection.</p>
            </aside>
        </section>
        <section id="beautyResults" class="customer-ui-panel beauty-results" hidden>
            <p class="customer-kicker">Generated results</p>
            <h2>Select your preferred preview</h2>
            <div id="beautyResultGrid" class="beauty-result-grid"></div>
        </section>
    <?php endif; ?>
</main>
<script>
window.SMARTBOOKING_BEAUTY_AI = <?= json_encode([
    'csrfToken' => $_SESSION['beauty_ai_csrf'],
    'healthEndpoint' => 'api/ai-health.php',
    'generateEndpoint' => 'api/generate-beauty-preview.php',
    'selectEndpoint' => 'api/select-beauty-preview.php',
    'demoImage' => '../assets/images/ai/demo-beauty-face.png',
], JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="../assets/js/ai-beauty-studio.js"></script>
<?php include '../includes/customer-footer.php'; ?>
