<?php
declare(strict_types=1);

$pageTitle = 'Services';

require_once '../includes/service-catalog.php';

$category = $_GET['category'] ?? 'all';
$categories = catalogCategories();
$services = catalogServices($category);

include '../includes/header.php';

$pageText = [
    'english' => [
        'kicker' => '',
        'title' => 'Choose the care, treatment, or transformation you need.',
        'subtitle' => 'Actual prices from the supplied pricelists. Displayed durations are scheduling estimates.',
        'categories' => 'Service categories',
        'all' => 'All services',
        'notice' => 'Advanced aesthetic services may require consultation before booking.',
        'search' => 'Search a service...',
        'services' => 'services',
        'popular' => 'Popular',
        'estimated' => 'Estimated',
        'minutes' => 'min',
        'no_results' => 'No service matches your search.',
    ],
    'filipino' => [
        'kicker' => '',
        'title' => 'Piliin ang pangangalaga, treatment, o pagbabagong kailangan mo.',
        'subtitle' => 'Aktuwal na presyo mula sa ibinigay na pricelist. Ang ipinakitang tagal ay pagtataya para sa iskedyul.',
        'categories' => 'Mga kategorya ng serbisyo',
        'all' => 'Lahat ng serbisyo',
        'notice' => 'Maaaring mangailangan ng konsultasyon ang advanced aesthetic services bago mag-book.',
        'search' => 'Maghanap ng serbisyo...',
        'services' => 'mga serbisyo',
        'popular' => 'Sikat',
        'estimated' => 'Tinatayang',
        'minutes' => 'minuto',
        'no_results' => 'Walang serbisyong tumutugma sa iyong hinahanap.',
    ],
    'taglish' => [
        'kicker' => '',
        'title' => 'Piliin ang care, treatment, o transformation na kailangan mo.',
        'subtitle' => 'Actual prices mula sa provided pricelists. Estimates lang ang displayed duration.',
        'categories' => 'Service categories',
        'all' => 'Lahat ng services',
        'notice' => 'Baka kailangan muna ng consultation ang advanced aesthetic services bago mag-book.',
        'search' => 'Search ng service...',
        'services' => 'services',
        'popular' => 'Popular',
        'estimated' => 'Estimated',
        'minutes' => 'min',
        'no_results' => 'Walang service na match sa search mo.',
    ],
];

$copy = $pageText[$languageCode] ?? $pageText['english'];

$categoryTranslations = [
    'english' => [
        'all' => 'All services',
        'nail-care' => 'Nail Care',
        'spa-massage' => 'Spa & Massage',
        'lashes-brows' => 'Lashes & Brows',
        'hair' => 'Hair Services',
        'facial-skin' => 'Facial & Skin',
        'hair-removal' => 'Waxing & Hair Removal',
        'advanced-aesthetic' => 'Advanced Aesthetic',
        'semi-permanent' => 'Semi-Permanent Makeup',
        'kiddie' => 'Kiddie Services',
    ],
    'filipino' => [
        'all' => 'Lahat ng serbisyo',
        'nail-care' => 'Pangangalaga ng Kuko',
        'spa-massage' => 'Spa at Masahe',
        'lashes-brows' => 'Pilikmata at Kilay',
        'hair' => 'Mga Serbisyo sa Buhok',
        'facial-skin' => 'Facial at Balat',
        'hair-removal' => 'Waxing at Hair Removal',
        'advanced-aesthetic' => 'Advanced Aesthetic',
        'semi-permanent' => 'Semi-Permanent Makeup',
        'kiddie' => 'Mga Serbisyo para sa Bata',
    ],
    'taglish' => [
        'all' => 'Lahat ng services',
        'nail-care' => 'Nail Care',
        'spa-massage' => 'Spa & Massage',
        'lashes-brows' => 'Lashes & Brows',
        'hair' => 'Hair Services',
        'facial-skin' => 'Facial & Skin',
        'hair-removal' => 'Waxing & Hair Removal',
        'advanced-aesthetic' => 'Advanced Aesthetic',
        'semi-permanent' => 'Semi-Permanent Makeup',
        'kiddie' => 'Kiddie Services',
    ],
];

$categoryLabels = $categoryTranslations[$languageCode]
    ?? $categoryTranslations['english'];
?>

<main class="services-page">
    <section class="services-hero">
        <p class="eyebrow">
            <?= htmlspecialchars($copy['kicker'], ENT_QUOTES, 'UTF-8') ?>
        </p>

        <h1>
            <?= htmlspecialchars($copy['title'], ENT_QUOTES, 'UTF-8') ?>
        </h1>

        <p>
            <?= htmlspecialchars($copy['subtitle'], ENT_QUOTES, 'UTF-8') ?>
        </p>
    </section>

    <section class="catalog-layout">
        <aside class="catalog-sidebar">
            <button id="filterToggle">
                <?= htmlspecialchars($copy['categories'], ENT_QUOTES, 'UTF-8') ?>
            </button>

            <nav id="catalogFilters">
                <a
                    class="<?= $category === 'all' ? 'active' : '' ?>"
                    href="services.php"
                >
                    <?= htmlspecialchars($categoryLabels['all'], ENT_QUOTES, 'UTF-8') ?>
                    <span><?= count(catalogServices()) ?></span>
                </a>

                <?php foreach ($categories as $item): ?>
                    <a
                        class="<?= $category === $item['slug'] ? 'active' : '' ?>"
                        href="?category=<?= urlencode($item['slug']) ?>"
                    >
                        <?= htmlspecialchars(
                            $categoryLabels[$item['slug']] ?? $item['name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                        <span><?= count(catalogServices($item['slug'])) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>

            <p class="notice">
                <?= htmlspecialchars($copy['notice'], ENT_QUOTES, 'UTF-8') ?>
            </p>
        </aside>

        <div class="catalog-main">
            <div class="toolbar">
                <div>
                    <strong>
                        <?= count($services) ?>
                        <?= htmlspecialchars($copy['services'], ENT_QUOTES, 'UTF-8') ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars(
                            $categoryLabels[$category] ?? ucwords(str_replace('-', ' ', $category)),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>
                </div>

                <input
                    id="serviceSearch"
                    type="search"
                    placeholder="<?= htmlspecialchars($copy['search'], ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="catalog-grid">
                <?php foreach ($services as $service): ?>
                    <article
                        class="catalog-card"
                        data-search="<?= htmlspecialchars(
                            strtolower($service['name'] . ' ' . $service['description']),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >
                        <a href="service-details.php?name=<?= urlencode($service['name']) ?>">
                            <div class="catalog-image">
                                <img
                                    loading="lazy"
                                    src="<?= htmlspecialchars($service['image'], ENT_QUOTES, 'UTF-8') ?>"
                                    alt="<?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?>"
                                >

                                <?php if ($service['featured']): ?>
                                    <span>
                                        <?= htmlspecialchars($copy['popular'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="catalog-copy">
                                <p>
                                    <?= htmlspecialchars(
                                        $categoryLabels[$service['category']]
                                            ?? ucwords(str_replace('-', ' ', $service['category'])),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </p>

                                <h2><?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?></h2>

                                <em><?= htmlspecialchars($service['description'], ENT_QUOTES, 'UTF-8') ?></em>

                                <div>
                                    <strong><?= catalogMoney($service['price']) ?></strong>

                                    <small>
                                        <?= $service['price_note']
                                            ? htmlspecialchars($service['price_note'], ENT_QUOTES, 'UTF-8') . ' · '
                                            : '' ?>
                                        <?= htmlspecialchars($copy['estimated'], ENT_QUOTES, 'UTF-8') ?>
                                        <?= (int) $service['duration'] ?>
                                        <?= htmlspecialchars($copy['minutes'], ENT_QUOTES, 'UTF-8') ?>
                                    </small>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>

            <div id="noResults" class="no-results" hidden>
                <?= htmlspecialchars($copy['no_results'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>
