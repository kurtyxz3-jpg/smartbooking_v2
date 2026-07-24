<?php
declare(strict_types=1);

$pageTitle = 'Beauty, Reimagined';

require_once __DIR__ . '/includes/service-catalog.php';

$catalog = serviceCatalog();
$categories = catalogCategories();
$featured = array_slice(
    array_values(
        array_filter(
            catalogServices(),
            static fn(array $service): bool => $service['featured']
        )
    ),
    0,
    8
);

$promotions = [
    [
        'badge' => 'New Guest Offer',
        'title' => 'Glow Starter Package',
        'description' => 'Enjoy a manicure, pedicure, and relaxing foot spa in one introductory visit.',
        'price' => '₱449',
        'original_price' => '₱500',
        'button' => 'Book this offer',
        'link' => 'customer/book.php?service=Manicure',
        'image' => '/smartbooking_v2/assets/images/home-packages/package-foot-spa.webp',
        'theme' => 'gold',
    ],
    [
        'badge' => 'Weekday Beauty Treat',
        'title' => 'Gel Polish Pair',
        'description' => 'Book gel polish for hands and feet from Monday to Thursday and save on your set.',
        'price' => '₱649',
        'original_price' => '₱750',
        'button' => 'Choose gel polish',
        'link' => 'customer/book.php?service=Gel%20Polish%20-%20Hands',
        'image' => '/smartbooking_v2/assets/images/services/nails/nails-14.webp',
        'theme' => 'rose',
    ],
    [
        'badge' => 'Relax & Renew',
        'title' => 'Massage + Foot Spa',
        'description' => 'A full hour of massage followed by a refreshing ladies’ or gentlemen’s foot spa.',
        'price' => '₱699',
        'original_price' => '₱800',
        'button' => 'Reserve relaxation',
        'link' => 'customer/book.php?service=Regular%20Massage%20-%201%20Hour',
        'image' => '/smartbooking_v2/assets/images/home-packages/package-massage.webp',
        'theme' => 'brown',
    ],
];

include __DIR__ . '/includes/header.php';
?>

<link rel="stylesheet" href="/smartbooking_v2/assets/css/homepage-enhancements.css">

<main>
    <section class="nbw-hero">
        <div class="nbw-hero-copy reveal">
            <p class="eyebrow">Beauty lounge · Pampanga</p>
            <h1>Real beauty work. A more beautiful way to book it.</h1>
            <p class="lead">
                Explore the actual Nails &amp; Body Worx services through the
                pictures and pricelists provided by the salon.
            </p>

            <div class="actions">
                <a class="nbw-primary-button" href="customer/services.php">
                    Explore <?= count(catalogServices()) ?> services ↗
                </a>
                <a class="nbw-secondary-button" href="#gallery">View real work</a>
            </div>

            <div class="proof">
                <div><strong><?= count($categories) ?></strong><span>collections</span></div>
                <div><strong><?= count(catalogServices()) ?>+</strong><span>services</span></div>
                <div><strong>91</strong><span>gallery images</span></div>
            </div>
        </div>

        <div class="nbw-collage reveal">
            <figure class="main">
                <img src="<?= $catalog['galleries']['nails'][9] ?>" alt="Nail art">
            </figure>
            <figure class="top">
                <img src="<?= $catalog['galleries']['salon'][2] ?>" alt="Salon">
            </figure>
            <figure class="bottom">
                <img src="<?= $catalog['galleries']['lips'][13] ?>" alt="Lip pigmentation">
            </figure>

            <div class="floating">
                <small>AI Nail Studio</small>
                <strong>3 designs from one idea</strong>
            </div>
        </div>
    </section>

    <!-- Automatically moving category ribbon -->
    <section class="category-marquee" aria-label="Service categories">
        <div class="category-marquee-fade category-marquee-fade-left"></div>
        <div class="category-marquee-fade category-marquee-fade-right"></div>

        <div class="category-marquee-track" id="categoryMarqueeTrack">
            <?php for ($repeat = 0; $repeat < 2; $repeat++): ?>
                <div class="category-marquee-group" aria-hidden="<?= $repeat === 1 ? 'true' : 'false' ?>">
                    <?php foreach ($categories as $category): ?>
                        <a href="customer/services.php?category=<?= urlencode($category['slug']) ?>">
                            <img
                                src="<?= htmlspecialchars($category['image'], ENT_QUOTES, 'UTF-8') ?>"
                                alt=""
                            >
                            <span><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></span>
                            <b><?= count(catalogServices($category['slug'])) ?></b>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endfor; ?>
        </div>
    </section>

    <section class="section">
        <div class="section-heading reveal">
            <div>
                <p class="eyebrow">Actual menu</p>
                <h2>Popular services from the supplied pricelist.</h2>
            </div>

            <div>
                <p>
                    Prices are based on the uploaded salon materials. Durations
                    are estimates for scheduling and can be edited later.
                </p>
                <a href="customer/services.php">See the complete menu →</a>
            </div>
        </div>

        <div class="service-grid">
            <?php foreach ($featured as $service): ?>
                <article class="service-card reveal">
                    <a href="customer/service-details.php?name=<?= urlencode($service['name']) ?>">
                        <div class="service-image">
                            <img
                                src="<?= htmlspecialchars($service['image'], ENT_QUOTES, 'UTF-8') ?>"
                                alt="<?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?>"
                            >
                            <span>
                                <?= htmlspecialchars(
                                    ucwords(str_replace('-', ' ', $service['category'])),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </span>
                        </div>

                        <div class="service-copy">
                            <h3><?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars($service['description'], ENT_QUOTES, 'UTF-8') ?></p>

                            <div>
                                <strong><?= catalogMoney($service['price']) ?></strong>
                                <small>
                                    <?= $service['price_note']
                                        ? htmlspecialchars($service['price_note'], ENT_QUOTES, 'UTF-8') . ' · '
                                        : '' ?>
                                    ~<?= (int) $service['duration'] ?> min
                                </small>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="lookbook" id="gallery">
        <div class="lookbook-copy">
            <p class="eyebrow">Real work, real space</p>
            <h2>The salon’s visual story.</h2>
            <p>
                Switch between nails, the salon, lip pigmentation, and service campaigns.
            </p>

            <div class="gallery-tabs">
                <button class="active" data-gallery-filter="nails">Nails</button>
                <button data-gallery-filter="salon">Salon &amp; Spa</button>
                <button data-gallery-filter="lips">Lip Pigmentation</button>
                <button data-gallery-filter="campaigns">Services</button>
            </div>
        </div>

        <div class="masonry" id="galleryGrid">
            <?php foreach (array_slice($catalog['galleries']['nails'], 0, 14) as $image): ?>
                <button class="gallery-item">
                    <img
                        loading="lazy"
                        src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>"
                        alt="Salon gallery"
                    >
                </button>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Packages now have matching photographs -->
    <section class="packages packages-enhanced" id="packages">
        <div class="package-main">
            <img
                src="/smartbooking_v2/assets/images/services/campaigns/campaigns-29.webp"
                alt="Gel polish and foot spa package"
            >

            <div>
                <p class="eyebrow">Package spotlight</p>
                <h2>Gel hand + gel feet + foot spa</h2>
                <strong>₱888</strong>
                <a href="customer/services.php?category=nail-care">
                    Browse nail care →
                </a>
            </div>
        </div>

        <div class="package-list package-list-with-images">
            <article
                class="package-photo-card"
                style="--package-image:url('/smartbooking_v2/assets/images/home-packages/package-foot-spa.webp')"
            >
                <div class="package-photo-overlay"></div>
                <div class="package-photo-content">
                    <span>Package 1</span>
                    <h3>Manicure + Pedicure + Foot Spa</h3>
                    <strong>₱500</strong>
                    <a href="customer/book.php?service=Manicure">Book package ↗</a>
                </div>
            </article>

            <article
                class="package-photo-card"
                style="--package-image:url('/smartbooking_v2/assets/images/home-packages/package-massage.webp')"
            >
                <div class="package-photo-overlay"></div>
                <div class="package-photo-content">
                    <span>Package 11</span>
                    <h3>Manicure + Pedicure + Foot Spa + Massage</h3>
                    <strong>₱980</strong>
                    <a href="customer/book.php?service=Regular%20Massage%20-%201%20Hour">
                        Book package ↗
                    </a>
                </div>
            </article>

            <article
                class="package-photo-card"
                style="--package-image:url('/smartbooking_v2/assets/images/home-packages/package-softgel.webp')"
            >
                <div class="package-photo-overlay"></div>
                <div class="package-photo-content">
                    <span>Package 18</span>
                    <h3>Softgel Extension + Gel Polish + Pedicure</h3>
                    <strong>₱900</strong>
                    <a href="customer/book.php?service=Softgel%20Extension%20with%20Gel%20Polish">
                        Book package ↗
                    </a>
                </div>
            </article>
        </div>
    </section>

    <section class="pricelists">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Transparent pricing</p>
                <h2>Original salon pricelists.</h2>
            </div>
            <p>
                Click any card to enlarge the original material supplied by the business.
            </p>
        </div>

        <div class="price-scroll">
            <?php foreach (catalogPricelists() as $pricelistImage): ?>
                <button
                    data-lightbox-src="<?= htmlspecialchars($pricelistImage, ENT_QUOTES, 'UTF-8') ?>"
                >
                    <img
                        src="<?= htmlspecialchars($pricelistImage, ENT_QUOTES, 'UTF-8') ?>"
                        alt="Original Nails and Body Worx pricelist"
                    >
                </button>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="visit" id="visit">
        <img src="<?= $catalog['galleries']['salon'][20] ?>" alt="Salon interior">

        <div>
            <p class="eyebrow">Visit the lounge</p>
            <h2>A warm, active beauty space.</h2>
            <p>
                The supplied images now become the website’s visual identity
                rather than generic stock photos.
            </p>
            <a class="nbw-primary-button" href="customer/book.php">
                Reserve an appointment ↗
            </a>
        </div>
    </section>
</main>

<!-- Promotional popup: three offers -->
<div
    class="promotion-modal"
    id="promotionModal"
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    aria-labelledby="promotionTitle"
>
    <div class="promotion-backdrop" data-promotion-close></div>

    <section class="promotion-dialog">
        <button
            class="promotion-close"
            type="button"
            data-promotion-close
            aria-label="Close promotions"
        >
            ×
        </button>

        <div class="promotion-visual">
            <?php foreach ($promotions as $index => $promotion): ?>
                <article
                    class="promotion-slide <?= $index === 0 ? 'active' : '' ?>"
                    data-promotion-slide="<?= $index ?>"
                >
                    <img
                        src="<?= htmlspecialchars($promotion['image'], ENT_QUOTES, 'UTF-8') ?>"
                        alt="<?= htmlspecialchars($promotion['title'], ENT_QUOTES, 'UTF-8') ?>"
                    >
                </article>
            <?php endforeach; ?>

            <div class="promotion-brand">
                <span>N</span>
                <strong>Nails &amp; Body Worx</strong>
            </div>
        </div>

        <div class="promotion-content">
            <p class="promotion-kicker">Limited-time salon offers</p>

            <?php foreach ($promotions as $index => $promotion): ?>
                <article
                    class="promotion-copy <?= $index === 0 ? 'active' : '' ?>"
                    data-promotion-copy="<?= $index ?>"
                    data-promotion-theme="<?= htmlspecialchars($promotion['theme'], ENT_QUOTES, 'UTF-8') ?>"
                >
                    <span><?= htmlspecialchars($promotion['badge'], ENT_QUOTES, 'UTF-8') ?></span>
                    <h2 id="<?= $index === 0 ? 'promotionTitle' : '' ?>">
                        <?= htmlspecialchars($promotion['title'], ENT_QUOTES, 'UTF-8') ?>
                    </h2>
                    <p><?= htmlspecialchars($promotion['description'], ENT_QUOTES, 'UTF-8') ?></p>

                    <div class="promotion-price">
                        <strong><?= htmlspecialchars($promotion['price'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <del><?= htmlspecialchars($promotion['original_price'], ENT_QUOTES, 'UTF-8') ?></del>
                    </div>

                    <a
                        href="<?= htmlspecialchars($promotion['link'], ENT_QUOTES, 'UTF-8') ?>"
                        class="promotion-action"
                    >
                        <?= htmlspecialchars($promotion['button'], ENT_QUOTES, 'UTF-8') ?> ↗
                    </a>
                </article>
            <?php endforeach; ?>

            <div class="promotion-controls">
                <button type="button" data-promotion-prev aria-label="Previous promotion">←</button>

                <div class="promotion-dots">
                    <?php foreach ($promotions as $index => $promotion): ?>
                        <button
                            type="button"
                            class="<?= $index === 0 ? 'active' : '' ?>"
                            data-promotion-dot="<?= $index ?>"
                            aria-label="Show promotion <?= $index + 1 ?>"
                        ></button>
                    <?php endforeach; ?>
                </div>

                <button type="button" data-promotion-next aria-label="Next promotion">→</button>
            </div>

            <label class="promotion-hide">
                <input type="checkbox" id="promotionHideToday">
                Do not show again today
            </label>
        </div>
    </section>
</div>

<script>
window.NBW_GALLERIES =
    <?= json_encode($catalog['galleries'], JSON_UNESCAPED_SLASHES) ?>;
</script>

<script src="/smartbooking_v2/assets/js/homepage-enhancements.js"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
