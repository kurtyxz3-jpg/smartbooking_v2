<?php
declare(strict_types=1);
$pageTitle='Service Details';
require_once '../config/database.php';
require_once '../includes/service-catalog.php';
require_once '../includes/service-image-library.php';
$serviceId=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$service=null;
if($serviceId){
 $statement=$pdo->prepare("SELECT s.*,c.name category_name FROM services s LEFT JOIN service_categories c ON c.id=s.category_id WHERE s.id=? AND s.status='active' LIMIT 1");
 $statement->execute([$serviceId]);$service=$statement->fetch(PDO::FETCH_ASSOC)?:null;
}
if(!$service)http_response_code(404);
include '../includes/header.php';
?>
<main class="detail-page"><?php if(!$service):?><section class="not-found"><h1>Service not found.</h1><a class="nbw-primary-button" href="services.php">Return to services</a></section><?php else:$categoryName=(string)($service['category_name']??'Uncategorized');$categorySlug=strtolower(trim(preg_replace('/[^a-z0-9]+/','-',$categoryName)??'','-'));$photo=smartBookingServiceImage((string)$service['name'],$categoryName);?><section class="detail-hero"><div class="detail-image"><img src="../assets/images/admin-service-library/<?=htmlspecialchars($photo)?>" alt="<?=htmlspecialchars((string)$service['name'])?>"></div><div class="detail-copy"><p class="eyebrow"><?=htmlspecialchars($categoryName)?></p><h1><?=htmlspecialchars((string)$service['name'])?></h1><p><?=htmlspecialchars(trim((string)($service['description']??''))?:'Professional salon service from Nails & Body Worx.')?></p><div class="detail-price"><strong><?=catalogMoney((float)$service['price'])?></strong><span><?=!empty($service['price_note'])?htmlspecialchars((string)$service['price_note']).' &middot; ':''?>Estimated <?=(int)($service['duration']??60)?> minutes</span></div><?php if(str_contains(strtolower((string)($service['description']??'')),'consultation')):?><div class="consultation"><strong>Consultation required</strong><span>Suitability, final price, and treatment details must be confirmed by the salon.</span></div><?php endif;?><div class="detail-actions"><a class="nbw-primary-button" href="book.php?service_id=<?=(int)$service['id']?>&amp;service=<?=urlencode((string)$service['name'])?>">Book this service &#8599;</a><a class="nbw-secondary-button" href="services.php?category=<?=urlencode($categorySlug)?>">Similar services</a></div></div></section><section class="detail-notes"><article><span>01</span><h2>Before your visit</h2><p>Share allergies, sensitivities, medical conditions, and previous procedures with the salon.</p></article><article><span>02</span><h2>Timing</h2><p>Displayed duration is the salon's current scheduling estimate.</p></article><article><span>03</span><h2>Final pricing</h2><p>Range-based and consultation services may vary according to assessment and complexity.</p></article></section><?php endif;?></main>
<?php include '../includes/footer.php';?>
