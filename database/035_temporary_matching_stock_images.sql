USE smartbooking_v2;

-- Temporary matching stock-image paths for spa and massage services.

UPDATE services
SET image = 'assets/images/services/temporary-stock/spa/foot-spa.webp'
WHERE name = 'Foot Spa - Ladies';

UPDATE services
SET image = 'assets/images/services/temporary-stock/spa/foot-spa.webp'
WHERE name = 'Foot Spa - Gentlemen';

UPDATE services
SET image = 'assets/images/services/temporary-stock/spa/hand-scrub.webp'
WHERE name = 'Hand Scrub';

UPDATE services
SET image = 'assets/images/services/temporary-stock/spa/hot-stone-massage.webp'
WHERE name = 'Regular Massage - 1 Hour';

UPDATE services
SET image = 'assets/images/services/temporary-stock/spa/hot-stone-massage.webp'
WHERE name = 'Massage with Stretching - 1 Hour';

UPDATE services
SET image = 'assets/images/services/temporary-stock/spa/hot-stone-massage.webp'
WHERE name = 'Ventosa Massage - 1 Hour';

UPDATE services
SET image = 'assets/images/services/temporary-stock/spa/hot-stone-massage.webp'
WHERE name = 'Massage with Ear Candle - 1 Hour';

UPDATE services
SET image = 'assets/images/services/temporary-stock/spa/hot-stone-massage.webp'
WHERE name = 'Hot Stone Massage - 1.5 Hours';

UPDATE services
SET image = 'assets/images/services/temporary-stock/spa/hot-stone-massage.webp'
WHERE name = 'Back Massage - 30 Minutes';

UPDATE services
SET image = 'assets/images/services/temporary-stock/spa/hand-massage.webp'
WHERE name = 'Hand Massage - 30 Minutes';

UPDATE services
SET image = 'assets/images/services/temporary-stock/spa/foot-massage.webp'
WHERE name = 'Foot Massage - 30 Minutes';
