<?php
$pageTitle='Beauty, Reimagined';
require_once __DIR__.'/includes/service-catalog.php';
$catalog=serviceCatalog();
$categories=catalogCategories();
$featured=array_slice(array_values(array_filter(catalogServices(),fn($s)=>$s['featured'])),0,8);
include __DIR__.'/includes/header.php';
?>
<main>
<section class="nbw-hero">
<div class="nbw-hero-copy reveal"><p class="eyebrow">Beauty lounge · Pampanga</p>
<h1>Real beauty work. A more beautiful way to book it.</h1>
<p class="lead">Explore the actual Nails &amp; Body Worx services through the pictures and pricelists provided by the salon.</p>
<div class="actions"><a class="nbw-primary-button" href="customer/services.php">Explore <?=count(catalogServices())?> services ↗</a><a class="nbw-secondary-button" href="#gallery">View real work</a></div>
<div class="proof"><div><strong><?=count($categories)?></strong><span>collections</span></div><div><strong><?=count(catalogServices())?>+</strong><span>services</span></div><div><strong>91</strong><span>gallery images</span></div></div>
</div>
<div class="nbw-collage reveal">
<figure class="main"><img src="<?=$catalog['galleries']['nails'][9]?>" alt="Nail art"></figure>
<figure class="top"><img src="<?=$catalog['galleries']['salon'][2]?>" alt="Salon"></figure>
<figure class="bottom"><img src="<?=$catalog['galleries']['lips'][13]?>" alt="Lip pigmentation"></figure>
<div class="floating"><small>AI Nail Studio</small><strong>3 designs from one idea</strong></div>
</div>
</section>

<section class="category-strip">
<?php foreach($categories as $category): ?>
<a href="customer/services.php?category=<?=urlencode($category['slug'])?>"><img src="<?=htmlspecialchars($category['image'])?>" alt=""><span><?=htmlspecialchars($category['name'])?></span></a>
<?php endforeach; ?>
</section>

<section class="section">
<div class="section-heading reveal"><div><p class="eyebrow">Actual menu</p><h2>Popular services from the supplied pricelist.</h2></div><div><p>Prices are based on the uploaded salon materials. Durations are estimates for scheduling and can be edited later.</p><a href="customer/services.php">See the complete menu →</a></div></div>
<div class="service-grid">
<?php foreach($featured as $service): ?>
<article class="service-card reveal"><a href="customer/service-details.php?name=<?=urlencode($service['name'])?>">
<div class="service-image"><img src="<?=htmlspecialchars($service['image'])?>" alt="<?=htmlspecialchars($service['name'])?>"><span><?=htmlspecialchars(ucwords(str_replace('-',' ',$service['category'])))?></span></div>
<div class="service-copy"><h3><?=htmlspecialchars($service['name'])?></h3><p><?=htmlspecialchars($service['description'])?></p><div><strong><?=catalogMoney($service['price'])?></strong><small><?=$service['price_note']?htmlspecialchars($service['price_note']).' · ':''?>~<?=intval($service['duration'])?> min</small></div></div>
</a></article>
<?php endforeach; ?>
</div>
</section>

<section class="lookbook" id="gallery">
<div class="lookbook-copy"><p class="eyebrow">Real work, real space</p><h2>The salon’s visual story.</h2><p>Switch between nails, the salon, lip pigmentation, and service campaigns.</p>
<div class="gallery-tabs"><button class="active" data-gallery-filter="nails">Nails</button><button data-gallery-filter="salon">Salon &amp; Spa</button><button data-gallery-filter="lips">Lip Pigmentation</button><button data-gallery-filter="campaigns">Services</button></div></div>
<div class="masonry" id="galleryGrid"><?php foreach(array_slice($catalog['galleries']['nails'],0,14) as $image): ?><button class="gallery-item"><img loading="lazy" src="<?=htmlspecialchars($image)?>" alt="Salon gallery"></button><?php endforeach; ?></div>
</section>

<section class="packages" id="packages">
<div class="package-main"><img src="/smartbooking_v2/assets/images/services/campaigns/campaigns-29.webp" alt="Package"><div><p class="eyebrow">Package spotlight</p><h2>Gel hand + gel feet + foot spa</h2><strong>₱888</strong><a href="customer/services.php?category=nail-care">Browse nail care →</a></div></div>
<div class="package-list"><article><span>Package 1</span><h3>Manicure + Pedicure + Foot Spa</h3><strong>₱500</strong></article><article><span>Package 11</span><h3>Manicure + Pedicure + Foot Spa + Massage</h3><strong>₱980</strong></article><article><span>Package 18</span><h3>Softgel Extension + Gel Polish + Pedicure</h3><strong>₱900</strong></article></div>
</section>

<section class="pricelists"><div class="section-heading"><div><p class="eyebrow">Transparent pricing</p><h2>Original salon pricelists.</h2></div><p>Click any card to enlarge the original material supplied by the business.</p></div><div class="price-scroll">
    
<?php foreach (catalogPricelists() as $pricelistImage): ?>
    <button data-lightbox-src="<?= htmlspecialchars($pricelistImage, ENT_QUOTES, 'UTF-8') ?>">
        <img
            src="<?= htmlspecialchars($pricelistImage, ENT_QUOTES, 'UTF-8') ?>"
            alt="Original Nails and Body Worx pricelist"
        >
    </button>
<?php endforeach; ?>

</div></section>

<section class="visit" id="visit"><img src="<?=$catalog['galleries']['salon'][20]?>" alt="Salon interior"><div><p class="eyebrow">Visit the lounge</p><h2>A warm, active beauty space.</h2><p>The supplied images now become the website’s visual identity rather than generic stock photos.</p><a class="nbw-primary-button" href="customer/book.php">Reserve an appointment ↗</a></div></section>
</main>
<script>window.NBW_GALLERIES=<?=json_encode($catalog['galleries'],JSON_UNESCAPED_SLASHES)?>;</script>
<?php include __DIR__.'/includes/footer.php';?>
