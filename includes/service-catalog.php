<?php
declare(strict_types=1);

$catalog = json_decode(<<<'JSON'
{
  "categories": [
    {
      "slug": "nail-care",
      "name": "Nail Care",
      "description": "Manicure, pedicure, gel polish, extensions, removal, and nail art.",
      "image": "/smartbooking_v2/assets/images/services/nails/nails-01.webp"
    },
    {
      "slug": "spa-massage",
      "name": "Spa & Massage",
      "description": "Foot spa, massage, reflexology, scrubs, and relaxing body care.",
      "image": "/smartbooking_v2/assets/images/services/salon/salon-02.webp"
    },
    {
      "slug": "lashes-brows",
      "name": "Lashes & Brows",
      "description": "Lash lifting, tint, threading, extensions, and brow lamination.",
      "image": "/smartbooking_v2/assets/images/services/salon/salon-16.webp"
    },
    {
      "slug": "hair",
      "name": "Hair Services",
      "description": "Haircut, color, highlights, rebond, Brazilian, and Botox treatments.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-04.webp"
    },
    {
      "slug": "facial-skin",
      "name": "Facial & Skin",
      "description": "Facials, peels, carbon laser, hydration, and skin treatments.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-07.webp"
    },
    {
      "slug": "hair-removal",
      "name": "Waxing & Hair Removal",
      "description": "Waxing and diode-laser treatments for different body areas.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-15.webp"
    },
    {
      "slug": "advanced-aesthetic",
      "name": "Advanced Aesthetic",
      "description": "Contouring, lifting, fillers, Hiko nose lift, and advanced treatments.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-11.webp"
    },
    {
      "slug": "semi-permanent",
      "name": "Semi-Permanent Makeup",
      "description": "Brows, eyeliner, lip micropigmentation, and reconstruction.",
      "image": "/smartbooking_v2/assets/images/services/lips/lips-01.webp"
    },
    {
      "slug": "kiddie",
      "name": "Kiddie Services",
      "description": "Gentle nail and spa care for younger guests.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-23.webp"
    }
  ],
  "services": [
    {
      "category": "nail-care",
      "name": "Manicure",
      "price": 120,
      "duration": 30,
      "description": "Classic nail shaping, cuticle care, and regular polish.",
      "image": "/smartbooking_v2/assets/images/services/nails/nails-04.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "nail-care",
      "name": "Pedicure",
      "price": 130,
      "duration": 40,
      "description": "Classic foot and nail care with regular polish.",
      "image": "/smartbooking_v2/assets/images/services/salon/salon-01.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "nail-care",
      "name": "Gel Polish - Hands",
      "price": 350,
      "duration": 60,
      "description": "Long-wearing gel color for the hands.",
      "image": "/smartbooking_v2/assets/images/services/nails/nails-14.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "nail-care",
      "name": "Gel Polish - Feet",
      "price": 400,
      "duration": 60,
      "description": "Long-wearing gel color for the feet.",
      "image": "/smartbooking_v2/assets/images/services/salon/salon-24.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "nail-care",
      "name": "Softgel Extension with Gel Polish",
      "price": 800,
      "duration": 120,
      "description": "Softgel extensions finished with your selected gel color.",
      "image": "/smartbooking_v2/assets/images/services/nails/nails-10.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "nail-care",
      "name": "Softgel per Nail",
      "price": 100,
      "duration": 20,
      "description": "Single-nail softgel extension or repair.",
      "image": "/smartbooking_v2/assets/images/services/nails/nails-11.webp",
      "featured": false,
      "price_note": "per nail"
    },
    {
      "category": "nail-care",
      "name": "Softgel Removal",
      "price": 200,
      "duration": 45,
      "description": "Careful removal of a full softgel set.",
      "image": "/smartbooking_v2/assets/images/services/nails/nails-08.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "nail-care",
      "name": "Polygel/Acrylic Removal",
      "price": 250,
      "duration": 50,
      "description": "Removal of polygel or acrylic extensions.",
      "image": "/smartbooking_v2/assets/images/services/nails/nails-09.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "nail-care",
      "name": "Polygel Infill with Gel Polish",
      "price": 600,
      "duration": 100,
      "description": "Polygel maintenance with fresh gel polish.",
      "image": "/smartbooking_v2/assets/images/services/nails/nails-13.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "nail-care",
      "name": "Nail Art",
      "price": 150,
      "duration": 30,
      "description": "Custom nail-art details based on complexity.",
      "image": "/smartbooking_v2/assets/images/services/nails/nails-01.webp",
      "featured": true,
      "price_note": "₱150-₱200"
    },
    {
      "category": "nail-care",
      "name": "Stones and Embellishments",
      "price": 20,
      "duration": 10,
      "description": "Decorative stones or embellishments.",
      "image": "/smartbooking_v2/assets/images/services/nails/nails-02.webp",
      "featured": false,
      "price_note": "₱20-₱50 each"
    },
    {
      "category": "nail-care",
      "name": "French Tip - Regular Polish",
      "price": 50,
      "duration": 20,
      "description": "Classic French-tip add-on using regular polish.",
      "image": "/smartbooking_v2/assets/images/services/nails/nails-05.webp",
      "featured": false,
      "price_note": "add-on"
    },
    {
      "category": "nail-care",
      "name": "Change Gel Polish",
      "price": 250,
      "duration": 45,
      "description": "Refresh your current set with a new gel color.",
      "image": "/smartbooking_v2/assets/images/services/nails/nails-16.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "nail-care",
      "name": "Gel Polish Removal",
      "price": 50,
      "duration": 20,
      "description": "Gentle gel-polish removal.",
      "image": "/smartbooking_v2/assets/images/services/nails/nails-17.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "spa-massage",
      "name": "Foot Spa - Ladies",
      "price": 300,
      "duration": 50,
      "description": "Soak, exfoliation, callus care, and relaxing foot treatment.",
      "image": "/smartbooking_v2/assets/images/services/salon/salon-05.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "spa-massage",
      "name": "Foot Spa - Gentlemen",
      "price": 350,
      "duration": 50,
      "description": "Foot spa care tailored for gentlemen.",
      "image": "/smartbooking_v2/assets/images/services/salon/salon-08.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "spa-massage",
      "name": "Hand Scrub",
      "price": 250,
      "duration": 30,
      "description": "Exfoliating hand treatment for smoother skin.",
      "image": "/smartbooking_v2/assets/images/services/salon/salon-10.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "spa-massage",
      "name": "Whitening Mask",
      "price": 100,
      "duration": 25,
      "description": "Brightening mask add-on for hands or feet.",
      "image": "/smartbooking_v2/assets/images/services/salon/salon-12.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "spa-massage",
      "name": "Whitening Full Body Scrub",
      "price": 800,
      "duration": 75,
      "description": "Full-body exfoliating and brightening treatment.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-05.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "spa-massage",
      "name": "Regular Massage - 1 Hour",
      "price": 500,
      "duration": 60,
      "description": "Relaxing full-body massage.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-19.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "spa-massage",
      "name": "Massage with Stretching - 1 Hour",
      "price": 600,
      "duration": 60,
      "description": "Massage paired with guided stretching.",
      "image": "/smartbooking_v2/assets/images/services/salon/salon-03.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "spa-massage",
      "name": "Ventosa Massage - 1 Hour",
      "price": 650,
      "duration": 60,
      "description": "Massage with traditional ventosa treatment.",
      "image": "/smartbooking_v2/assets/images/services/salon/salon-06.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "spa-massage",
      "name": "Massage with Ear Candle - 1 Hour",
      "price": 680,
      "duration": 60,
      "description": "Full massage combined with ear candling.",
      "image": "/smartbooking_v2/assets/images/services/salon/salon-07.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "spa-massage",
      "name": "Hot Stone Massage - 1.5 Hours",
      "price": 800,
      "duration": 90,
      "description": "Heated-stone massage for deep relaxation.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-30.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "spa-massage",
      "name": "Head Spa - 1 Hour",
      "price": 1200,
      "duration": 60,
      "description": "Scalp cleansing, massage, and hair-care ritual.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-24.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "spa-massage",
      "name": "Back Massage - 30 Minutes",
      "price": 350,
      "duration": 30,
      "description": "Focused back massage.",
      "image": "/smartbooking_v2/assets/images/services/salon/salon-22.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "spa-massage",
      "name": "Hand Massage - 30 Minutes",
      "price": 250,
      "duration": 30,
      "description": "Focused hand and arm massage.",
      "image": "/smartbooking_v2/assets/images/services/salon/salon-23.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "spa-massage",
      "name": "Foot Massage - 30 Minutes",
      "price": 300,
      "duration": 30,
      "description": "Focused foot and lower-leg massage.",
      "image": "/smartbooking_v2/assets/images/services/salon/salon-04.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "lashes-brows",
      "name": "Threading",
      "price": 100,
      "duration": 25,
      "description": "Precision facial threading.",
      "image": "/smartbooking_v2/assets/images/services/salon/salon-16.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "lashes-brows",
      "name": "Eyelash Lift",
      "price": 500,
      "duration": 60,
      "description": "Natural lash-lifting treatment.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-25.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "lashes-brows",
      "name": "Eyelash Lift with Tint",
      "price": 600,
      "duration": 75,
      "description": "Lash lift enhanced with tint.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-27.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "lashes-brows",
      "name": "Eyelash Extension Removal",
      "price": 100,
      "duration": 30,
      "description": "Gentle eyelash-extension removal.",
      "image": "/smartbooking_v2/assets/images/services/salon/salon-17.webp",
      "featured": false,
      "price_note": "₱100-₱150"
    },
    {
      "category": "lashes-brows",
      "name": "Brow Lamination",
      "price": 500,
      "duration": 60,
      "description": "Brow shaping and lamination.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-09.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "kiddie",
      "name": "Kiddie Manicure",
      "price": 50,
      "duration": 25,
      "description": "Gentle manicure for children.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-23.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "kiddie",
      "name": "Kiddie Pedicure",
      "price": 50,
      "duration": 25,
      "description": "Gentle pedicure for children.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-23.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "kiddie",
      "name": "Kiddie Polish Only",
      "price": 30,
      "duration": 15,
      "description": "Quick polish application for children.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-23.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "kiddie",
      "name": "Kiddie Foot Spa",
      "price": 120,
      "duration": 35,
      "description": "Gentle foot spa for children.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-23.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "kiddie",
      "name": "Kiddie Package",
      "price": 250,
      "duration": 70,
      "description": "Manicure, pedicure, foot spa, and mask.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-23.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "hair",
      "name": "Hair Cut",
      "price": 150,
      "duration": 35,
      "description": "Professional salon hair service based on the supplied pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-04.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "hair",
      "name": "Blower",
      "price": 100,
      "duration": 30,
      "description": "Professional salon hair service based on the supplied pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-04.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "hair",
      "name": "Shampoo",
      "price": 100,
      "duration": 20,
      "description": "Professional salon hair service based on the supplied pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-04.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "hair",
      "name": "Hair Iron",
      "price": 200,
      "duration": 40,
      "description": "Professional salon hair service based on the supplied pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-04.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "hair",
      "name": "Hair All-in Package",
      "price": 350,
      "duration": 70,
      "description": "Professional salon hair service based on the supplied pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-04.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "hair",
      "name": "Hair Color - Short",
      "price": 600,
      "duration": 100,
      "description": "Professional salon hair service based on the supplied pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-04.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "hair",
      "name": "Hair Color - Medium",
      "price": 900,
      "duration": 120,
      "description": "Professional salon hair service based on the supplied pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-04.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "hair",
      "name": "Hair Color - Long",
      "price": 1100,
      "duration": 150,
      "description": "Professional salon hair service based on the supplied pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-04.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "hair",
      "name": "Highlights - Short",
      "price": 600,
      "duration": 100,
      "description": "Professional salon hair service based on the supplied pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-04.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "hair",
      "name": "Brazilian Treatment - Short",
      "price": 600,
      "duration": 120,
      "description": "Professional salon hair service based on the supplied pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-04.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "hair",
      "name": "Hair Botox - Short",
      "price": 600,
      "duration": 120,
      "description": "Professional salon hair service based on the supplied pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-04.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "hair",
      "name": "Rebond - Short",
      "price": 1200,
      "duration": 180,
      "description": "Professional salon hair service based on the supplied pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-04.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "hair",
      "name": "Rebond + Color - Short",
      "price": 1700,
      "duration": 210,
      "description": "Professional salon hair service based on the supplied pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-04.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "facial-skin",
      "name": "Regular Facial with Mask",
      "price": 500,
      "duration": 60,
      "description": "Facial cleansing and mask treatment.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-07.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "facial-skin",
      "name": "Anti-aging 24K Gold Mask",
      "price": 650,
      "duration": 60,
      "description": "Facial with a 24K gold mask.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-31.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "facial-skin",
      "name": "Diamond Peel",
      "price": 700,
      "duration": 60,
      "description": "Exfoliating diamond-peel facial.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-26.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "facial-skin",
      "name": "Regular Facial + RF Face",
      "price": 800,
      "duration": 75,
      "description": "Regular facial combined with RF face treatment.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-08.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "facial-skin",
      "name": "Supreme Diamond Peel",
      "price": 850,
      "duration": 75,
      "description": "Enhanced diamond-peel treatment.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-26.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "facial-skin",
      "name": "Melasma Laser Removal",
      "price": 1000,
      "duration": 60,
      "description": "Laser treatment for melasma concerns; consultation required.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-13.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "facial-skin",
      "name": "Korean BB Glow",
      "price": 1200,
      "duration": 90,
      "description": "BB Glow facial treatment.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-07.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "facial-skin",
      "name": "Blackdoll Carbon Laser",
      "price": 1200,
      "duration": 75,
      "description": "Carbon laser facial treatment.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-13.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "facial-skin",
      "name": "Hydra 3-in-1 Facial",
      "price": 1500,
      "duration": 90,
      "description": "Multi-step hydrating facial treatment.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-26.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "facial-skin",
      "name": "Ultimate Hydra 10-in-1 Facial",
      "price": 2000,
      "duration": 120,
      "description": "Comprehensive multi-step hydrating facial.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-26.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "facial-skin",
      "name": "Glutathione Drip",
      "price": 1500,
      "duration": 60,
      "description": "Glutathione drip service; consultation required.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-03.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "hair-removal",
      "name": "Underarm Waxing",
      "price": 250,
      "duration": 30,
      "description": "Hair-removal service based on the supplied salon pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-15.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "hair-removal",
      "name": "Full Arms Waxing",
      "price": 400,
      "duration": 45,
      "description": "Hair-removal service based on the supplied salon pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-15.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "hair-removal",
      "name": "Half Legs Waxing",
      "price": 450,
      "duration": 45,
      "description": "Hair-removal service based on the supplied salon pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-15.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "hair-removal",
      "name": "Full Legs Waxing",
      "price": 650,
      "duration": 60,
      "description": "Hair-removal service based on the supplied salon pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-15.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "hair-removal",
      "name": "Bikini Waxing",
      "price": 700,
      "duration": 45,
      "description": "Hair-removal service based on the supplied salon pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-15.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "hair-removal",
      "name": "Diode Laser - Underarm",
      "price": 700,
      "duration": 45,
      "description": "Hair-removal service based on the supplied salon pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-06.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "hair-removal",
      "name": "Diode Laser - Bikini",
      "price": 800,
      "duration": 45,
      "description": "Hair-removal service based on the supplied salon pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-06.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "hair-removal",
      "name": "Diode Laser - Full Arms",
      "price": 1100,
      "duration": 60,
      "description": "Hair-removal service based on the supplied salon pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-14.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "hair-removal",
      "name": "Diode Laser - Full Legs",
      "price": 1500,
      "duration": 75,
      "description": "Hair-removal service based on the supplied salon pricelist.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-14.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "advanced-aesthetic",
      "name": "12D HIFU",
      "price": 4500,
      "duration": 90,
      "description": "Face contouring and lifting treatment; consultation required.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-11.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "advanced-aesthetic",
      "name": "EMSculpt - 30 Minutes",
      "price": 1000,
      "duration": 30,
      "description": "Non-invasive body contouring session.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-10.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "advanced-aesthetic",
      "name": "RF Face",
      "price": 350,
      "duration": 30,
      "description": "Radio-frequency face treatment.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-08.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "advanced-aesthetic",
      "name": "Mesolipo - Double Chin",
      "price": 1500,
      "duration": 60,
      "description": "Mesolipo treatment for the double-chin area; consultation required.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-17.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "advanced-aesthetic",
      "name": "Lip Filler",
      "price": 8000,
      "duration": 60,
      "description": "Hyaluronic-acid lip filler; consultation required.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-16.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "advanced-aesthetic",
      "name": "Chin Filler",
      "price": 8000,
      "duration": 60,
      "description": "Hyaluronic-acid chin filler; consultation required.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-16.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "advanced-aesthetic",
      "name": "Jaw Filler",
      "price": 15000,
      "duration": 75,
      "description": "Jaw filler treatment; consultation required.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-16.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "advanced-aesthetic",
      "name": "Hiko Nose Lift - Basic/Retouch",
      "price": 5500,
      "duration": 75,
      "description": "Basic or retouch Hiko nose-lift package; consultation required.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-22.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "advanced-aesthetic",
      "name": "Warts/Skin Tag Removal",
      "price": 800,
      "duration": 45,
      "description": "Warts or skin-tag removal; consultation required.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-20.webp",
      "featured": false,
      "price_note": "₱800-₱2,500"
    },
    {
      "category": "advanced-aesthetic",
      "name": "Body Tattoo Removal",
      "price": 1000,
      "duration": 60,
      "description": "Body tattoo-removal session; consultation required.",
      "image": "/smartbooking_v2/assets/images/services/campaigns/campaigns-20.webp",
      "featured": false,
      "price_note": "₱1,000-₱2,500"
    },
    {
      "category": "semi-permanent",
      "name": "Ombre Brows - First Session",
      "price": 5500,
      "duration": 150,
      "description": "Semi-permanent ombre-brow procedure.",
      "image": "/smartbooking_v2/assets/images/services/semi-permanent/ombre-brows.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "semi-permanent",
      "name": "Nano Combo Brows - First Session",
      "price": 4500,
      "duration": 150,
      "description": "Nano combo-brow procedure.",
      "image": "/smartbooking_v2/assets/images/services/semi-permanent/nano-combo-brows.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "semi-permanent",
      "name": "Nano Machine Strokes - First Session",
      "price": 7900,
      "duration": 180,
      "description": "Nano machine-stroke brow procedure.",
      "image": "/smartbooking_v2/assets/images/services/semi-permanent/nano-machine-strokes.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "semi-permanent",
      "name": "Menstrokes - First Session",
      "price": 7900,
      "duration": 180,
      "description": "Semi-permanent brow procedure designed for men.",
      "image": "/smartbooking_v2/assets/images/services/semi-permanent/menstrokes.webp",
      "featured": false,
      "price_note": ""
    },
    {
      "category": "semi-permanent",
      "name": "Lip Micropigmentation - First Session",
      "price": 4500,
      "duration": 180,
      "description": "Semi-permanent lip-color procedure.",
      "image": "/smartbooking_v2/assets/images/services/semi-permanent/lip-micropigmentation.webp",
      "featured": true,
      "price_note": ""
    },
    {
      "category": "semi-permanent",
      "name": "Classic/Winged Eyeliner Tattoo - First Session",
      "price": 3500,
      "duration": 150,
      "description": "Semi-permanent eyeliner procedure.",
      "image": "/smartbooking_v2/assets/images/services/semi-permanent/eyeliner-tattoo.webp",
      "featured": false,
      "price_note": "₱3,500-₱4,000"
    },
    {
      "category": "semi-permanent",
      "name": "Brow Reconstruction",
      "price": 500,
      "duration": 45,
      "description": "Cover-up and correction consultation starts at the listed charge.",
      "image": "/smartbooking_v2/assets/images/services/semi-permanent/brow-reconstruction.webp",
      "featured": false,
      "price_note": "charges vary"
    }
  ],
  "galleries": {
    "nails": [
      "/smartbooking_v2/assets/images/services/nails/nails-01.webp",
      "/smartbooking_v2/assets/images/services/nails/nails-02.webp",
      "/smartbooking_v2/assets/images/services/nails/nails-03.webp",
      "/smartbooking_v2/assets/images/services/nails/nails-04.webp",
      "/smartbooking_v2/assets/images/services/nails/nails-05.webp",
      "/smartbooking_v2/assets/images/services/nails/nails-06.webp",
      "/smartbooking_v2/assets/images/services/nails/nails-07.webp",
      "/smartbooking_v2/assets/images/services/nails/nails-08.webp",
      "/smartbooking_v2/assets/images/services/nails/nails-09.webp",
      "/smartbooking_v2/assets/images/services/nails/nails-10.webp",
      "/smartbooking_v2/assets/images/services/nails/nails-11.webp",
      "/smartbooking_v2/assets/images/services/nails/nails-12.webp",
      "/smartbooking_v2/assets/images/services/nails/nails-13.webp",
      "/smartbooking_v2/assets/images/services/nails/nails-14.webp",
      "/smartbooking_v2/assets/images/services/nails/nails-15.webp",
      "/smartbooking_v2/assets/images/services/nails/nails-16.webp",
      "/smartbooking_v2/assets/images/services/nails/nails-17.webp",
      "/smartbooking_v2/assets/images/services/nails/nails-18.webp",
      "/smartbooking_v2/assets/images/services/nails/nails-19.webp"
    ],
    "salon": [
      "/smartbooking_v2/assets/images/services/salon/salon-01.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-02.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-03.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-04.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-05.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-06.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-07.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-08.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-09.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-10.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-11.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-12.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-13.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-14.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-15.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-16.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-17.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-18.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-19.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-20.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-21.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-22.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-23.webp",
      "/smartbooking_v2/assets/images/services/salon/salon-24.webp"
    ],
    "lips": [
      "/smartbooking_v2/assets/images/services/lips/lips-01.webp",
      "/smartbooking_v2/assets/images/services/lips/lips-02.webp",
      "/smartbooking_v2/assets/images/services/lips/lips-03.webp",
      "/smartbooking_v2/assets/images/services/lips/lips-04.webp",
      "/smartbooking_v2/assets/images/services/lips/lips-05.webp",
      "/smartbooking_v2/assets/images/services/lips/lips-06.webp",
      "/smartbooking_v2/assets/images/services/lips/lips-07.webp",
      "/smartbooking_v2/assets/images/services/lips/lips-08.webp",
      "/smartbooking_v2/assets/images/services/lips/lips-09.webp",
      "/smartbooking_v2/assets/images/services/lips/lips-10.webp",
      "/smartbooking_v2/assets/images/services/lips/lips-11.webp",
      "/smartbooking_v2/assets/images/services/lips/lips-12.webp",
      "/smartbooking_v2/assets/images/services/lips/lips-13.webp",
      "/smartbooking_v2/assets/images/services/lips/lips-14.webp",
      "/smartbooking_v2/assets/images/services/lips/lips-15.webp",
      "/smartbooking_v2/assets/images/services/lips/lips-16.webp",
      "/smartbooking_v2/assets/images/services/lips/lips-17.webp"
    ],
    "campaigns": [
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-01.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-02.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-03.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-04.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-05.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-06.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-07.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-08.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-09.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-10.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-11.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-12.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-13.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-14.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-15.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-16.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-17.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-18.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-19.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-20.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-21.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-22.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-23.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-24.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-25.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-26.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-27.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-28.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-29.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-30.webp",
      "/smartbooking_v2/assets/images/services/campaigns/campaigns-31.webp"
    ]
  }
}
JSON, true, 512, JSON_THROW_ON_ERROR);

function serviceCatalog(): array { global $catalog; return $catalog; }
function catalogCategories(): array { return serviceCatalog()['categories']; }

function catalogServices(?string $category = null): array
{
    $services = serviceCatalog()['services'];
    if (!$category || $category === 'all') return $services;

    return array_values(array_filter(
        $services,
        static fn(array $service): bool => $service['category'] === $category
    ));
}

function catalogServiceByName(string $name): ?array
{
    foreach (catalogServices() as $service) {
        if ($service['name'] === $name) return $service;
    }
    return null;
}

function catalogMoney(float|int $amount): string
{
    return '₱' . number_format((float)$amount, 0);
}

/**
 * Original pricelist pages shown on the public homepage.
 *
 * pricelist-03.webp is intentionally excluded because it is the
 * Nails & Body Worx cover page rather than an actual price-list page.
 */
function catalogPricelists(): array
{
    return [
        '/smartbooking_v2/assets/images/services/pricelists/pricelist-01.webp',
        '/smartbooking_v2/assets/images/services/pricelists/pricelist-02.webp',
        '/smartbooking_v2/assets/images/services/pricelists/pricelist-04.webp',
        '/smartbooking_v2/assets/images/services/pricelists/pricelist-05.webp',
        '/smartbooking_v2/assets/images/services/pricelists/pricelist-06.webp',
        '/smartbooking_v2/assets/images/services/pricelists/pricelist-07.webp',
        '/smartbooking_v2/assets/images/services/pricelists/pricelist-08.webp',
        '/smartbooking_v2/assets/images/services/pricelists/pricelist-09.webp',
    ];
}

