<?php
declare(strict_types=1);

$pageTitle = 'Services';

require_once '../includes/service-catalog.php';
require_once '../includes/service-image-library.php';
require_once '../config/database.php';

$category = $_GET['category'] ?? 'all';

function customerServiceCategorySlug(string $name): string
{
    $slug = strtolower(trim($name));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    return trim($slug, '-');
}

$categoryRows = $pdo->query(
    "SELECT id, name
     FROM service_categories
     WHERE status = 'active'
     ORDER BY display_order ASC, name ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$categories = [];
$categoryIds = [];
foreach ($categoryRows as $row) {
    $slug = customerServiceCategorySlug((string) $row['name']);
    $categories[] = ['id' => (int) $row['id'], 'name' => $row['name'], 'slug' => $slug];
    $categoryIds[$slug] = (int) $row['id'];
}

$serviceSql = "SELECT s.*, c.name AS category_name
               FROM services s
               LEFT JOIN service_categories c ON c.id = s.category_id
               WHERE s.status = 'active'";
$serviceParams = [];
if ($category !== 'all' && isset($categoryIds[$category])) {
    $serviceSql .= ' AND s.category_id = ?';
    $serviceParams[] = $categoryIds[$category];
} elseif ($category !== 'all') {
    $category = 'all';
}
$serviceSql .= ' ORDER BY s.featured DESC, s.display_order ASC, s.name ASC';
$serviceStatement = $pdo->prepare($serviceSql);
$serviceStatement->execute($serviceParams);
$serviceRows = $serviceStatement->fetchAll(PDO::FETCH_ASSOC);

$services = array_map(static function (array $row): array {
    $categoryName = (string) ($row['category_name'] ?? 'Uncategorized');
    return [
        'id' => (int) $row['id'],
        'name' => (string) $row['name'],
        'description' => trim((string) ($row['description'] ?? '')) ?: 'Professional salon service from Nails & Body Worx.',
        'price' => (float) ($row['price'] ?? 0),
        'price_note' => (string) ($row['price_note'] ?? ''),
        'duration' => (int) ($row['duration'] ?? 60),
        'featured' => (bool) ($row['featured'] ?? false),
        'category' => customerServiceCategorySlug($categoryName),
        'category_name' => $categoryName,
    ];
}, $serviceRows);

$allServiceCount = (int) $pdo->query("SELECT COUNT(*) FROM services WHERE status='active'")->fetchColumn();
$categoryServiceCounts = [];
$countRows = $pdo->query("SELECT category_id, COUNT(*) total FROM services WHERE status='active' GROUP BY category_id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($countRows as $countRow) $categoryServiceCounts[(int) $countRow['category_id']] = (int) $countRow['total'];

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
                    <span><?= $allServiceCount ?></span>
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
                        <span><?= $categoryServiceCounts[(int) $item['id']] ?? 0 ?></span>
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
                    <?php $serviceImage = smartBookingServiceImage((string) $service['name'], (string) ($service['category'] ?? '')); ?>
                    <article
                        class="catalog-card"
                        data-search="<?= htmlspecialchars(
                            strtolower($service['name'] . ' ' . $service['description']),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >
                        <a href="service-details.php?id=<?= (int) $service['id'] ?>">
                            <div class="catalog-image">
                                <img
                                    loading="lazy"
                                    src="../assets/images/admin-service-library/<?= htmlspecialchars($serviceImage, ENT_QUOTES, 'UTF-8') ?>"
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
