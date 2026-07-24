<?php
declare(strict_types=1);

$pageTitle = 'AI Nail Studio';
$customerPage = 'ai';

require_once '../includes/customer-header.php';
?>

<link rel="stylesheet" href="../assets/css/customer-ai-studio.css">

<main class="customer-page-shell">
    <header class="customer-page-heading customer-ai-heading">
        <div class="customer-page-heading-copy">
            <p class="customer-kicker">Phase 2 · AI Nail Studio</p>
            <h1>Turn one idea into three nail concepts.</h1>
            <p>
                Choose your preferred colors, nail shape, length, style,
                occasion, and decorative details. SmartBooking will send your
                request to the Python AI service and display three concepts.
            </p>
        </div>

        <div class="customer-ai-server-chip" id="customerAiServerChip">
            <span></span>
            Checking AI server
        </div>
    </header>

    <div class="customer-ai-workspace">
        <section class="customer-ui-panel customer-ai-form-panel">
            <div class="customer-panel-title">
                <div>
                    <p class="customer-kicker">Design preferences</p>
                    <h2>Build your request</h2>
                </div>
            </div>

            <form id="customerAiForm" novalidate>
                <div class="customer-form-grid">
                    <div class="customer-form-group full">
                        <label for="preferred_color">Preferred colors</label>
                        <input
                            id="preferred_color"
                            name="color"
                            placeholder="Example: Nude, white, and gold"
                            maxlength="120"
                            required
                        >
                        <small>Use one color or describe a complete palette.</small>
                    </div>

                    <div class="customer-form-group">
                        <label for="nail_shape">Nail shape</label>
                        <select id="nail_shape" name="nail_shape">
                            <option>Almond</option>
                            <option>Round</option>
                            <option>Oval</option>
                            <option>Square</option>
                            <option>Coffin</option>
                            <option>Stiletto</option>
                        </select>
                    </div>

                    <div class="customer-form-group">
                        <label for="nail_length">Nail length</label>
                        <select id="nail_length" name="nail_length">
                            <option>Short</option>
                            <option selected>Medium</option>
                            <option>Long</option>
                            <option>Extra long</option>
                        </select>
                    </div>

                    <div class="customer-form-group">
                        <label for="design_style">Design style</label>
                        <select id="design_style" name="style">
                            <option>Elegant minimalist</option>
                            <option>Classic French</option>
                            <option>Floral</option>
                            <option>Chrome</option>
                            <option>Luxury glam</option>
                            <option>Marble</option>
                            <option>Seasonal</option>
                            <option>Abstract</option>
                        </select>
                    </div>

                    <div class="customer-form-group">
                        <label for="occasion">Occasion</label>
                        <select id="occasion" name="occasion">
                            <option>Everyday</option>
                            <option>Wedding guest</option>
                            <option>Bridal</option>
                            <option>Birthday</option>
                            <option>Graduation</option>
                            <option>Formal event</option>
                            <option>Holiday</option>
                        </select>
                    </div>

                    <fieldset class="customer-ai-addons full">
                        <legend>Add-ons</legend>

                        <div class="customer-ai-addon-grid">
                            <?php foreach ([
                                'Gold foil',
                                'Small rhinestones',
                                'Glitter',
                                'Chrome finish',
                                'Pearls',
                                'Floral accents',
                                'Charms',
                                'Marble details',
                            ] as $addOn): ?>
                                <label>
                                    <input
                                        type="checkbox"
                                        name="add_ons[]"
                                        value="<?= htmlspecialchars($addOn, ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                    <span><?= htmlspecialchars($addOn, ENT_QUOTES, 'UTF-8') ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>

                    <div class="customer-form-group full">
                        <label for="instructions">Special instructions</label>
                        <textarea
                            id="instructions"
                            name="instructions"
                            maxlength="600"
                            placeholder="Describe the finish, pattern, mood, or details you want the AI to include."
                        ></textarea>

                        <div class="customer-ai-character-count">
                            <span id="customerAiCharacterCount">0</span>/600
                        </div>
                    </div>
                </div>

                <div class="customer-ai-message" id="customerAiMessage" hidden></div>

                <div class="customer-form-actions">
                    <button
                        class="customer-ui-button primary customer-ai-generate-button"
                        id="customerAiGenerateButton"
                        type="submit"
                    >
                        <span class="customer-ai-button-label">Generate 3 designs ↗</span>
                        <span class="customer-ai-button-loading" hidden>
                            <i></i> Creating concepts
                        </span>
                    </button>
                </div>
            </form>
        </section>

        <section class="customer-ai-preview">
            <div class="customer-ai-preview-inner">
                <div class="customer-ai-preview-art">
                    <span style="--r:-10deg"></span>
                    <span style="--r:-4deg"></span>
                    <span style="--r:3deg"></span>
                    <span style="--r:9deg"></span>
                </div>

                <p class="customer-kicker">SmartBooking AI</p>
                <h2>Three directions from one idea.</h2>
                <p>
                    Demo mode currently produces concept cards. The same PHP
                    connection will continue working when the real image model
                    replaces the demo provider.
                </p>

                <ul class="customer-ai-preview-list">
                    <li>Three automatically generated results</li>
                    <li>Design selection and saved history</li>
                    <li>Ready to attach to a future booking</li>
                </ul>
            </div>
        </section>
    </div>

    <section
        class="customer-ui-panel customer-ai-results-panel"
        id="customerAiResults"
        hidden
    >
        <div class="customer-panel-title">
            <div>
                <p class="customer-kicker">Generated concepts</p>
                <h2>Choose your preferred design</h2>
                <p id="customerAiResultMessage">
                    Select one result to save it to your SmartBooking account.
                </p>
            </div>

            <button
                class="customer-ui-button secondary"
                id="customerAiGenerateAgain"
                type="button"
            >
                Generate again
            </button>
        </div>

        <div class="customer-ai-result-grid" id="customerAiResultGrid"></div>
    </section>

    <section
        class="customer-ui-panel customer-ai-selected-panel"
        id="customerAiSelectedPanel"
        hidden
    >
        <div class="customer-panel-title">
            <div>
                <p class="customer-kicker">Selected design</p>
                <h2>Your preferred concept is saved.</h2>
                <p>
                    You can continue to the booking page and mention this
                    design in your appointment instructions.
                </p>
            </div>

            <a class="customer-ui-button primary" href="book.php">
                Book this design ↗
            </a>
        </div>
    </section>
</main>

<script>
window.SMARTBOOKING_AI_CONFIG = {
    generateEndpoint: 'api/generate-nail-designs.php',
    selectEndpoint: 'api/select-nail-design.php',
    healthEndpoint: 'api/ai-health.php'
};
</script>
<script src="../assets/js/customer-ai-studio.js"></script>

<?php include '../includes/customer-footer.php'; ?>
