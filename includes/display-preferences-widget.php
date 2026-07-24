<?php
declare(strict_types=1);

require_once __DIR__ . '/language.php';

$widgetLabels = [
    'english' => [
        'button' => 'Language & display',
        'title' => 'Language & display',
        'language' => 'Language',
        'appearance' => 'Appearance',
        'default' => 'Default',
        'light' => 'Light',
        'dark' => 'Dark',
        'close' => 'Close preferences',
        'note' => 'Preferences are saved on this device.',
    ],
    'filipino' => [
        'button' => 'Wika at display',
        'title' => 'Wika at display',
        'language' => 'Wika',
        'appearance' => 'Itsura',
        'default' => 'Default',
        'light' => 'Maliwanag',
        'dark' => 'Madilim',
        'close' => 'Isara ang preferences',
        'note' => 'Naka-save ang preferences sa device na ito.',
    ],
    'taglish' => [
        'button' => 'Language at display',
        'title' => 'Language at display',
        'language' => 'Language',
        'appearance' => 'Appearance',
        'default' => 'Default',
        'light' => 'Light',
        'dark' => 'Dark',
        'close' => 'Close preferences',
        'note' => 'Saved ang preferences sa device na ito.',
    ],
];

$labels = $widgetLabels[$languageCode] ?? $widgetLabels['english'];
?>
<div
    class="nbw-preferences"
    id="nbwPreferences"
    data-current-language="<?= htmlspecialchars($languageCode, ENT_QUOTES, 'UTF-8') ?>"
    data-current-theme="<?= htmlspecialchars($themeMode, ENT_QUOTES, 'UTF-8') ?>"
>
    <button
        class="nbw-preferences-trigger"
        id="nbwPreferencesTrigger"
        type="button"
        aria-expanded="false"
        aria-controls="nbwPreferencesPanel"
    >
        <span aria-hidden="true">Aa</span>
        <strong><?= htmlspecialchars($labels['button'], ENT_QUOTES, 'UTF-8') ?></strong>
    </button>

    <section
        class="nbw-preferences-panel"
        id="nbwPreferencesPanel"
        aria-hidden="true"
        aria-labelledby="nbwPreferencesTitle"
    >
        <header>
            <div>
                <small>NBW Accessibility</small>
                <h2 id="nbwPreferencesTitle">
                    <?= htmlspecialchars($labels['title'], ENT_QUOTES, 'UTF-8') ?>
                </h2>
            </div>

            <button
                class="nbw-preferences-close"
                id="nbwPreferencesClose"
                type="button"
                aria-label="<?= htmlspecialchars($labels['close'], ENT_QUOTES, 'UTF-8') ?>"
            >
                ×
            </button>
        </header>

        <div class="nbw-preference-group">
            <span><?= htmlspecialchars($labels['language'], ENT_QUOTES, 'UTF-8') ?></span>

            <div class="nbw-preference-options" role="group" aria-label="Language">
                <?php foreach ([
                    'english' => 'English',
                    'filipino' => 'Filipino',
                    'taglish' => 'Taglish',
                ] as $code => $name): ?>
                    <button
                        type="button"
                        data-language-choice="<?= $code ?>"
                        class="<?= $languageCode === $code ? 'active' : '' ?>"
                        aria-pressed="<?= $languageCode === $code ? 'true' : 'false' ?>"
                    >
                        <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="nbw-preference-group">
            <span><?= htmlspecialchars($labels['appearance'], ENT_QUOTES, 'UTF-8') ?></span>

            <div class="nbw-theme-options" role="group" aria-label="Appearance">
                <?php foreach ([
                    'default' => $labels['default'],
                    'light' => $labels['light'],
                    'dark' => $labels['dark'],
                ] as $mode => $name): ?>
                    <button
                        type="button"
                        data-theme-choice="<?= $mode ?>"
                        class="<?= $themeMode === $mode ? 'active' : '' ?>"
                        aria-pressed="<?= $themeMode === $mode ? 'true' : 'false' ?>"
                    >
                        <i class="nbw-theme-preview nbw-theme-preview-<?= $mode ?>" aria-hidden="true"></i>
                        <span><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <p class="nbw-preferences-note">
            <?= htmlspecialchars($labels['note'], ENT_QUOTES, 'UTF-8') ?>
        </p>

        <div class="nbw-preferences-status" id="nbwPreferencesStatus" role="status" aria-live="polite"></div>
    </section>
</div>
