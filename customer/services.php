<?php
$pageTitle='Services';
require_once '../includes/service-catalog.php';
$category=$_GET['category']??'all';
$categories=catalogCategories();
$services=catalogServices($category);
include '../includes/header.php';
?>
<main class="services-page">
<section class="services-hero"><p class="eyebrow">Complete service menu</p><h1>Choose the care, treatment, or transformation you need.</h1><p>Actual prices from the supplied pricelists. Displayed durations are scheduling estimates.</p></section>
<section class="catalog-layout">
<aside class="catalog-sidebar"><button id="filterToggle">Service categories</button><nav id="catalogFilters"><a class="<?=$category==='all'?'active':''?>" href="services.php">All services <span><?=count(catalogServices())?></span></a><?php foreach($categories as $item):?><a class="<?=$category===$item['slug']?'active':''?>" href="?category=<?=urlencode($item['slug'])?>"><?=htmlspecialchars($item['name'])?><span><?=count(catalogServices($item['slug']))?></span></a><?php endforeach;?></nav><p class="notice">Advanced aesthetic services may require consultation before booking.</p></aside>
<div class="catalog-main"><div class="toolbar"><div><strong><?=count($services)?> services</strong><span><?=htmlspecialchars(ucwords(str_replace('-',' ',$category)))?></span></div><input id="serviceSearch" type="search" placeholder="Search a service..."></div>
<div class="catalog-grid"><?php foreach($services as $service):?><article class="catalog-card" data-search="<?=htmlspecialchars(strtolower($service['name'].' '.$service['description']))?>"><a href="service-details.php?name=<?=urlencode($service['name'])?>"><div class="catalog-image"><img loading="lazy" src="<?=htmlspecialchars($service['image'])?>" alt="<?=htmlspecialchars($service['name'])?>"><?php if($service['featured']):?><span>Popular</span><?php endif;?></div><div class="catalog-copy"><p><?=htmlspecialchars(ucwords(str_replace('-',' ',$service['category'])))?></p><h2><?=htmlspecialchars($service['name'])?></h2><em><?=htmlspecialchars($service['description'])?></em><div><strong><?=catalogMoney($service['price'])?></strong><small><?=$service['price_note']?htmlspecialchars($service['price_note']).' · ':''?>Estimated <?=intval($service['duration'])?> min</small></div></div></a></article><?php endforeach;?></div>
<div id="noResults" class="no-results" hidden>No service matches your search.</div></div>
</section></main>
<?php include '../includes/footer.php';?>
