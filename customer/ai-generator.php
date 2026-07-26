<?php
declare(strict_types=1);

$pageTitle = 'AI Nail Studio';
$customerPage = 'ai';
require_once '../includes/customer-header.php';
require_once '../includes/ai-service.php';

if (empty($_SESSION['ai_csrf'])) {
    $_SESSION['ai_csrf'] = bin2hex(random_bytes(32));
}
$settings = aiSettings($pdo);
$available = $settings['ai_enabled'] === '1'
    && $settings['nail_generator_enabled'] === '1';
$prefill = null;
$similarId = filter_input(INPUT_GET, 'similar_to', FILTER_VALIDATE_INT) ?: 0;
if ($similarId > 0) {
    require_once '../includes/ai-history-helper.php';
    $similar = aiDesignForCustomer($pdo, $similarId, (int) $_SESSION['user_id']);
    if ($similar) {
        $prefill = [
            'color' => $similar['colors'],
            'nail_shape' => $similar['nail_shape'],
            'nail_length' => $similar['nail_length'],
            'style' => $similar['design_style'],
            'occasion' => $similar['occasion'],
            'instructions' => $similar['instructions'],
            'add_ons' => json_decode((string) $similar['add_ons'], true) ?: [],
        ];
    }
}
?>
<link rel="stylesheet" href="../assets/css/phase2-ai-studio.css">
<link rel="stylesheet" href="../assets/css/ai-studio-tabs.css">

<main class="customer-page-shell phase2-ai-shell">
    <header class="customer-page-heading">
        <div>
            <p class="customer-kicker">Phase 2 · AI Nail Studio</p>
            <h1>Create three nail-design.</h1>
            <p>Choose your preferred details. SmartBooking sends them securely to the local Python AI service.</p>
        </div>
        <div class="phase2-heading-actions">
            <a class="phase2-saved-link" href="ai-designs.php">Saved previews</a>
            <span id="aiHealth" class="phase2-status-text">Checking AI service</span>
        </div>
    </header>

    <nav class="ai-studio-tabs" aria-label="AI beauty preview type">
        <a class="active" href="beauty-studio.php?area=nails">Nails</a>
        <a href="beauty-studio.php?area=lips">Lips</a>
        <a href="beauty-studio.php?area=eyebrows">Eyebrows</a>
    </nav>

    <?php if (!$available): ?>
        <section class="customer-ui-panel phase2-disabled">
            <h2>AI Nail Studio is currently unavailable.</h2>
            <p>A Super Administrator can enable it from AI Settings.</p>
        </section>
    <?php else: ?>
        <section class="phase2-ai-layout">
            <form id="aiStudioForm" class="customer-ui-panel phase2-ai-form">
                <h2>Design preferences</h2>
                <div class="phase2-color-fields">
                    <div class="phase2-honeycomb-field">
                        <span class="phase2-input-label">Choose from the color palette</span>
                        <div id="nailHoneycombPicker" class="phase2-honeycomb-picker"
                             aria-label="Choose a nail color">
                            <?php
                            $hslToHex = static function (float $hue, float $saturation, float $lightness): string {
                                $saturation /= 100;
                                $lightness /= 100;
                                $chroma = (1 - abs((2 * $lightness) - 1)) * $saturation;
                                $segment = fmod(($hue / 60), 6);
                                $secondary = $chroma * (1 - abs(fmod($segment, 2) - 1));
                                [$red, $green, $blue] = match ((int) floor($segment)) {
                                    0 => [$chroma, $secondary, 0],
                                    1 => [$secondary, $chroma, 0],
                                    2 => [0, $chroma, $secondary],
                                    3 => [0, $secondary, $chroma],
                                    4 => [$secondary, 0, $chroma],
                                    default => [$chroma, 0, $secondary],
                                };
                                $match = $lightness - ($chroma / 2);
                                return sprintf(
                                    '#%02X%02X%02X',
                                    (int) round(($red + $match) * 255),
                                    (int) round(($green + $match) * 255),
                                    (int) round(($blue + $match) * 255)
                                );
                            };
                            $colorName = static function (float $hue, float $distance): string {
                                if ($distance < .18) {
                                    return 'Soft white';
                                }
                                $names = ['Red', 'Orange', 'Yellow', 'Green', 'Turquoise',
                                    'Cyan', 'Blue', 'Violet', 'Magenta', 'Rose'];
                                return $names[((int) floor((fmod($hue + 18, 360)) / 36)) % 10];
                            };
                            $radius = 4;
                            for ($row = -$radius; $row <= $radius; $row++):
                                for ($column = -$radius; $column <= $radius; $column++):
                                    $thirdAxis = -$column - $row;
                                    if (abs($thirdAxis) > $radius) {
                                        continue;
                                    }
                                    $horizontal = $column + ($row / 2);
                                    $vertical = $row * .866;
                                    $distance = min(1, sqrt(($horizontal ** 2) + ($vertical ** 2)) / $radius);
                                    $hue = fmod(rad2deg(atan2($vertical, $horizontal)) + 390, 360);
                                    $hex = $hslToHex($hue, 92 * $distance, 97 - (49 * $distance));
                                    $name = $colorName($hue, $distance);
                            ?>
                                <button type="button" class="phase2-honeycomb-cell"
                                        data-color="<?= $hex ?>"
                                        data-color-name="<?= htmlspecialchars($name) ?>"
                                        style="--cell-color:<?= $hex ?>;--hex-x:<?= $horizontal ?>;--hex-y:<?= $row ?>"
                                        aria-label="Select <?= htmlspecialchars($name) ?> <?= $hex ?>"></button>
                            <?php
                                endfor;
                            endfor;
                            ?>
                            <div id="nailColorHover" class="phase2-color-hover" hidden>
                                <span id="nailColorHoverSwatch"></span>
                                <strong id="nailColorHoverName">Color</strong>
                                <small id="nailColorHoverHex">#FFFFFF</small>
                            </div>
                        </div>
                        <span class="phase2-picked-color">
                            <span id="nailColorSwatch" style="--picked-color:#C89B3C"></span>
                            <output id="nailColorHex">#C89B3C</output>
                        </span>
                    </div>
                    <label>Or type the color you want
                        <input id="nailColorText" name="color" maxlength="180" required
                               value="Gold" placeholder="Example: emerald green, nude pink, or #C89B3C">
                    </label>
                </div>
                <div class="phase2-fields">
                    <label>Nail length
                        <select name="nail_length">
                            <option>Short</option><option selected>Medium</option>
                            <option>Long</option><option>Extra long</option>
                        </select>
                    </label>
                    <label>Design style
                        <select name="style">
                            <option>Elegant minimalist</option><option>Classic French</option>
                            <option>Floral</option><option>Chrome</option>
                            <option>Luxury glam</option><option>Marble</option>
                        </select>
                    </label>
                    <label>Occasion
                        <select name="occasion">
                            <option>Everyday</option><option>Wedding guest</option>
                            <option>Bridal</option><option>Birthday</option>
                            <option>Graduation</option><option>Formal event</option>
                        </select>
                    </label>
                </div>
                <fieldset class="phase2-shape-fieldset">
                    <legend>Nail shape</legend>
                    <p class="phase2-field-help">Choose the nail silhouette you prefer.</p>
                    <div class="phase2-shape-grid">
                        <?php
                        $shapes = [
                            'Round', 'Square', 'Rounded Square', 'Oval',
                            'Squoval', 'Almond', 'Mountain Peak', 'Stiletto',
                            'Coffin', 'Ballerina', 'Lipstick', 'Cutout',
                        ];
                        foreach ($shapes as $shape):
                            $shapeFile = strtolower(str_replace(' ', '-', $shape));
                        ?>
                            <label class="phase2-shape-option">
                                <input type="radio" name="nail_shape"
                                       value="<?= htmlspecialchars($shape) ?>"
                                       <?= $shape === 'Almond' ? 'checked' : '' ?>>
                                <span class="phase2-shape-thumb shape-<?= htmlspecialchars($shapeFile) ?>"
                                      aria-hidden="true"></span>
                                <span class="phase2-shape-name"><?= htmlspecialchars($shape) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <fieldset>
                    <legend>Add-ons</legend>
                    <div class="phase2-addons">
                        <?php foreach (['Gold foil','Small rhinestones','Glitter','Chrome finish','Pearls','Floral accents'] as $item): ?>
                            <label><input type="checkbox" name="add_ons[]" value="<?= htmlspecialchars($item) ?>"> <?= htmlspecialchars($item) ?></label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <label>Special instructions
                    <textarea name="instructions" maxlength="600" placeholder="Optional details for the generated design"></textarea>
                </label>
                <div id="aiMessage" class="phase2-message" hidden></div>
                <button id="aiGenerateButton" class="customer-ui-button primary" type="submit">
                    Generate 3 designs
                </button>
            </form>

            <aside class="customer-ui-panel phase2-ai-info">
                <p class="customer-kicker">How it works</p>
                <h2>One request, three choices.</h2>
                <ol><li>Choose your nail preferences.</li><li>Python builds a salon-ready prompt.</li><li>Select a result and continue to booking.</li></ol>
                <p><strong>Daily limit:</strong> <?= (int) $settings['daily_customer_limit'] ?> requests per customer</p>
            </aside>
        </section>

        <section id="aiResults" class="customer-ui-panel phase2-results" hidden>
            <div><p class="customer-kicker">Generated results</p><h2>Select your preferred design</h2></div>
            <div id="aiResultGrid" class="phase2-result-grid"></div>
        </section>

        <div id="aiImageModal" class="phase2-image-modal" hidden
             role="dialog" aria-modal="true" aria-labelledby="aiModalTitle">
            <button id="aiModalBackdrop" class="phase2-modal-backdrop"
                    type="button" aria-label="Close image preview"></button>
            <section class="phase2-modal-panel">
                <header class="phase2-modal-header">
                    <div>
                        <p class="customer-kicker">Design preview</p>
                        <h2 id="aiModalTitle">Generated nail design</h2>
                    </div>
                    <button id="aiModalClose" class="phase2-modal-close"
                            type="button" aria-label="Close preview">&times;</button>
                </header>
                <div class="phase2-modal-image-wrap">
                    <button id="aiModalPrevious" class="phase2-modal-nav previous"
                            type="button" aria-label="Previous design">&#8249;</button>
                    <img id="aiModalImage" src="" alt="">
                    <button id="aiModalNext" class="phase2-modal-nav next"
                            type="button" aria-label="Next design">&#8250;</button>
                </div>
                <footer class="phase2-modal-footer">
                    <span>Use the arrows to compare all three designs.</span>
                    <a id="aiModalDownload" class="customer-ui-button secondary"
                       href="#" download>Download image</a>
                </footer>
            </section>
        </div>
    <?php endif; ?>
</main>

<script>
window.SMARTBOOKING_PHASE2_AI = <?= json_encode([
    'csrfToken' => $_SESSION['ai_csrf'],
    'healthEndpoint' => 'api/ai-health.php',
    'generateEndpoint' => 'api/generate-nail-designs.php',
    'selectEndpoint' => 'api/select-nail-design.php',
    'prefill' => $prefill,
], JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="../assets/js/phase2-ai-studio.js"></script>
<?php include '../includes/customer-footer.php'; ?>
