<?php
declare(strict_types=1);

function smartBookingServiceImage(string $serviceName, string $categoryName = ''): string
{
    $text = strtolower($serviceName . ' ' . $categoryName);
    $photos = [
        'eyeliner-tattoo.jpg' => ['eyeliner tattoo','winged eyeliner'],
        'tattoo-removal.jpg' => ['tattoo removal','laser brow removal'],
        'diode-laser.jpg' => ['diode','carbon laser','blackdoll','melasma laser'],
        'botox.jpg' => ['botox','jawtox','alartox','traptox','sweatox'],
        'injectables.jpg' => ['filler','hiko nose','glutathione drip'],
        'hifu.jpg' => ['hifu'],
        'body-contouring.jpg' => ['emsculpt','mesolipo','rf face','rf arms','rf legs','rf tummy','rf thigh'],
        'service-package.jpg' => ['package'],
        'ear-candling.jpg' => ['ear candl','ear candle'],
        'head-spa.jpg' => ['head spa'],
        'hand-scrub.jpg' => ['hand scrub'],
        'body-scrub.jpg' => ['body scrub','whitening full body'],
        'kids-haircut.jpg' => ['kid','child','junior'],
        'lip-blush.jpg' => ['lip','blush'],
        'waxing.jpg' => ['wax','thread'],
        'massage.jpg' => ['massage','spa','body'],
        'eyebrows.jpg' => ['brow','eyebrow'],
        'eyelashes.jpg' => ['lash','eyelash'],
        'facial.jpg' => ['facial','skin','rejuven','glow','diamond peel'],
        'hair-color.jpg' => ['color','dye','highlight','balayage','rebond','brazilian'],
        'haircut.jpg' => ['hair','cut','style','blow','shampoo','iron'],
        'nail-art.jpg' => ['nail art','gel','polish','acrylic','extension','stone','french tip'],
        'pedicure.jpg' => ['pedicure','foot','feet','callus'],
        'manicure.jpg' => ['manicure','nail','hand'],
    ];
    foreach ($photos as $photo => $keywords) {
        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) return $photo;
        }
    }
    $names = array_keys($photos);
    return $names[abs(crc32($serviceName)) % count($names)];
}

