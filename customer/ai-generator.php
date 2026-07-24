<?php
declare(strict_types=1);

$pageTitle = 'AI Nail Studio';
$customerPage = 'ai';

require_once '../includes/customer-header.php';
?>

<main class="customer-page-shell">
    <header class="customer-page-heading">
        <div class="customer-page-heading-copy">
            <p class="customer-kicker">Phase 2 preview</p>
            <h1>Turn one idea into three nail concepts.</h1>
            <p>
                Choose a color, design style, nail shape, and add-ons. The real
                image-generation connection will be activated during Phase 2.
            </p>
        </div>
    </header>

    <div class="customer-ai-workspace">
        <section class="customer-ui-panel">
            <div class="customer-panel-title">
                <div>
                    <p class="customer-kicker">Design preferences</p>
                    <h2>Build your request</h2>
                </div>
            </div>

            <form id="customerAiForm">
                <div class="customer-form-grid">
                    <div class="customer-form-group">
                        <label for="preferred_color">Preferred color</label>
                        <input id="preferred_color" placeholder="Nude, white, and gold" required>
                    </div>

                    <div class="customer-form-group">
                        <label for="nail_shape">Nail shape</label>
                        <select id="nail_shape">
                            <option>Almond</option>
                            <option>Round</option>
                            <option>Oval</option>
                            <option>Square</option>
                            <option>Coffin</option>
                            <option>Stiletto</option>
                        </select>
                    </div>

                    <div class="customer-form-group">
                        <label for="design_style">Design style</label>
                        <select id="design_style">
                            <option>Elegant</option>
                            <option>Minimalist</option>
                            <option>Floral</option>
                            <option>French</option>
                            <option>Chrome</option>
                            <option>Seasonal</option>
                        </select>
                    </div>

                    <div class="customer-form-group">
                        <label for="addons">Add-ons</label>
                        <input id="addons" placeholder="Gems, glitter, charms">
                    </div>

                    <div class="customer-form-group full">
                        <label for="instructions">Special instructions</label>
                        <textarea id="instructions" placeholder="Describe the occasion, pattern, finish, or anything you want included."></textarea>
                    </div>
                </div>

                <div class="customer-form-actions">
                    <button class="customer-ui-button primary" type="submit">
                        Preview 3 concepts ↗
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

                <p class="customer-kicker">AI Nail Studio</p>
                <h2>Your inspiration board starts here.</h2>
                <p>
                    This interface is ready. Phase 2 will connect it to the
                    image-generation service and save generated designs.
                </p>
            </div>
        </section>
    </div>

    <section class="customer-ui-panel" id="customerAiResults" hidden style="margin-top:15px">
        <div class="customer-panel-title">
            <div>
                <p class="customer-kicker">Concept preview</p>
                <h2>Three temporary style directions</h2>
            </div>
        </div>

        <div class="customer-ai-samples">
            <article class="customer-ai-sample" style="--sample:#d9b78c"><strong>Soft gold French</strong></article>
            <article class="customer-ai-sample" style="--sample:#c88f8b"><strong>Rose-glass shimmer</strong></article>
            <article class="customer-ai-sample" style="--sample:#eee3d8"><strong>Minimal pearl line</strong></article>
        </div>
    </section>
</main>

<?php include '../includes/customer-footer.php'; ?>
