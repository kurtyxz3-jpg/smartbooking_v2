-- SmartBooking V2 - Complete Nails & Body Worx service catalogue
-- Transcribed from the eight client-provided July 2026 pricelist images.
-- Existing rows are updated by slug; missing rows are inserted.

USE `smartbooking_v2`;

START TRANSACTION;

-- Ensure every required service category exists.
INSERT INTO `service_categories` (`name`, `description`, `status`, `display_order`) VALUES ('Body Contouring', NULL, 'active', 10) ON DUPLICATE KEY UPDATE `status` = 'active';
INSERT INTO `service_categories` (`name`, `description`, `status`, `display_order`) VALUES ('Facial & Skin', NULL, 'active', 20) ON DUPLICATE KEY UPDATE `status` = 'active';
INSERT INTO `service_categories` (`name`, `description`, `status`, `display_order`) VALUES ('Hair Services', NULL, 'active', 30) ON DUPLICATE KEY UPDATE `status` = 'active';
INSERT INTO `service_categories` (`name`, `description`, `status`, `display_order`) VALUES ('Injectables', NULL, 'active', 40) ON DUPLICATE KEY UPDATE `status` = 'active';
INSERT INTO `service_categories` (`name`, `description`, `status`, `display_order`) VALUES ('Kiddie Services', NULL, 'active', 50) ON DUPLICATE KEY UPDATE `status` = 'active';
INSERT INTO `service_categories` (`name`, `description`, `status`, `display_order`) VALUES ('Laser & Skin Treatments', NULL, 'active', 60) ON DUPLICATE KEY UPDATE `status` = 'active';
INSERT INTO `service_categories` (`name`, `description`, `status`, `display_order`) VALUES ('Lashes & Brows', NULL, 'active', 70) ON DUPLICATE KEY UPDATE `status` = 'active';
INSERT INTO `service_categories` (`name`, `description`, `status`, `display_order`) VALUES ('Nail Care', NULL, 'active', 80) ON DUPLICATE KEY UPDATE `status` = 'active';
INSERT INTO `service_categories` (`name`, `description`, `status`, `display_order`) VALUES ('Packages', NULL, 'active', 90) ON DUPLICATE KEY UPDATE `status` = 'active';
INSERT INTO `service_categories` (`name`, `description`, `status`, `display_order`) VALUES ('Semi-Permanent Makeup', NULL, 'active', 100) ON DUPLICATE KEY UPDATE `status` = 'active';
INSERT INTO `service_categories` (`name`, `description`, `status`, `display_order`) VALUES ('Spa & Massage', NULL, 'active', 110) ON DUPLICATE KEY UPDATE `status` = 'active';
INSERT INTO `service_categories` (`name`, `description`, `status`, `display_order`) VALUES ('Waxing', NULL, 'active', 120) ON DUPLICATE KEY UPDATE `status` = 'active';

-- A unique slug makes this catalogue safely re-importable.
ALTER TABLE `services` ADD UNIQUE INDEX IF NOT EXISTS `uq_services_slug` (`slug`);

INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Nail Care' LIMIT 1),
     'manicure', 'Manicure', NULL, 120.00, NULL,
     45, NULL, 0, 10, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Nail Care' LIMIT 1),
     'pedicure', 'Pedicure', NULL, 130.00, NULL,
     50, NULL, 0, 20, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Nail Care' LIMIT 1),
     'gel-polish-hands', 'Gel Polish - Hands', NULL, 350.00, NULL,
     60, NULL, 0, 30, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Nail Care' LIMIT 1),
     'gel-polish-feet', 'Gel Polish - Feet', NULL, 400.00, NULL,
     60, NULL, 0, 40, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Nail Care' LIMIT 1),
     'softgel-extension-with-gel-polish', 'Softgel Extension with Gel Polish', NULL, 800.00, NULL,
     120, NULL, 0, 50, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Nail Care' LIMIT 1),
     'softgel-extension-per-nail', 'Softgel Extension per Nail', NULL, 100.00, '₱100 per nail',
     20, NULL, 0, 60, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Nail Care' LIMIT 1),
     'softgel-removal', 'Softgel Removal', NULL, 200.00, NULL,
     45, NULL, 0, 70, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Nail Care' LIMIT 1),
     'softgel-removal-per-nail', 'Softgel Removal per Nail', NULL, 25.00, '₱25 per nail',
     10, NULL, 0, 80, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Nail Care' LIMIT 1),
     'polygel-or-acrylic-removal', 'Polygel or Acrylic Removal', NULL, 250.00, NULL,
     60, NULL, 0, 90, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Nail Care' LIMIT 1),
     'acrylic-removal-per-nail', 'Acrylic Removal per Nail', NULL, 30.00, '₱30 per nail',
     10, NULL, 0, 100, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Nail Care' LIMIT 1),
     'polygel-infill-with-gel-polish', 'Polygel Infill with Gel Polish', NULL, 600.00, NULL,
     90, NULL, 0, 110, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Nail Care' LIMIT 1),
     'nail-art', 'Nail Art', NULL, 150.00, '₱150–₱200',
     30, NULL, 0, 120, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Nail Care' LIMIT 1),
     'nail-stone-add-on', 'Nail Stone Add-on', NULL, 20.00, '₱20–₱50 each',
     10, NULL, 0, 130, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Nail Care' LIMIT 1),
     'french-tip-regular-polish', 'French Tip Regular Polish', NULL, 50.00, NULL,
     20, NULL, 0, 140, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Nail Care' LIMIT 1),
     'change-gel-polish', 'Change Gel Polish', NULL, 250.00, NULL,
     45, NULL, 0, 150, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Nail Care' LIMIT 1),
     'gel-polish-removal', 'Gel Polish Removal', NULL, 50.00, NULL,
     30, NULL, 0, 160, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Spa & Massage' LIMIT 1),
     'foot-spa-ladies', 'Foot Spa - Ladies', NULL, 300.00, NULL,
     60, NULL, 0, 170, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Spa & Massage' LIMIT 1),
     'foot-spa-gentlemen', 'Foot Spa - Gentlemen', NULL, 350.00, NULL,
     60, NULL, 0, 180, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Spa & Massage' LIMIT 1),
     'hand-scrub', 'Hand Scrub', NULL, 250.00, NULL,
     30, NULL, 0, 190, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Spa & Massage' LIMIT 1),
     'whitening-mask', 'Whitening Mask', NULL, 100.00, NULL,
     30, NULL, 0, 200, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Spa & Massage' LIMIT 1),
     'callus-remover', 'Callus Remover', NULL, 50.00, NULL,
     20, NULL, 0, 210, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Spa & Massage' LIMIT 1),
     'ear-candling', 'Ear Candling', NULL, 200.00, NULL,
     30, NULL, 0, 220, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Spa & Massage' LIMIT 1),
     'whitening-full-body-scrub', 'Whitening Full Body Scrub', NULL, 800.00, NULL,
     60, NULL, 0, 230, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Kiddie Services' LIMIT 1),
     'kiddie-manicure', 'Kiddie Manicure', NULL, 50.00, NULL,
     30, NULL, 0, 240, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Kiddie Services' LIMIT 1),
     'kiddie-pedicure', 'Kiddie Pedicure', NULL, 50.00, NULL,
     30, NULL, 0, 250, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Kiddie Services' LIMIT 1),
     'kiddie-polish-only', 'Kiddie Polish Only', NULL, 30.00, NULL,
     20, NULL, 0, 260, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Kiddie Services' LIMIT 1),
     'kiddie-foot-spa', 'Kiddie Foot Spa', NULL, 120.00, NULL,
     40, NULL, 0, 270, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Kiddie Services' LIMIT 1),
     'kiddie-whitening-mask', 'Kiddie Whitening Mask', NULL, 50.00, NULL,
     25, NULL, 0, 280, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Kiddie Services' LIMIT 1),
     'kiddie-package-mani-pedi-spa-and-mask', 'Kiddie Package - Mani, Pedi, Spa and Mask', NULL, 250.00, NULL,
     90, NULL, 0, 290, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Spa & Massage' LIMIT 1),
     'regular-massage-1-hour', 'Regular Massage - 1 Hour', NULL, 500.00, NULL,
     60, NULL, 0, 300, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Spa & Massage' LIMIT 1),
     'massage-with-stretching-1-hour', 'Massage with Stretching - 1 Hour', NULL, 600.00, NULL,
     60, NULL, 0, 310, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Spa & Massage' LIMIT 1),
     'ventosa-massage-1-hour', 'Ventosa Massage - 1 Hour', NULL, 650.00, NULL,
     60, NULL, 0, 320, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Spa & Massage' LIMIT 1),
     'massage-with-ear-candle-1-hour', 'Massage with Ear Candle - 1 Hour', NULL, 680.00, NULL,
     60, NULL, 0, 330, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Spa & Massage' LIMIT 1),
     'hot-stone-massage-1-5-hours', 'Hot Stone Massage - 1.5 Hours', NULL, 800.00, NULL,
     90, NULL, 0, 340, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Spa & Massage' LIMIT 1),
     'head-spa-1-hour', 'Head Spa - 1 Hour', NULL, 1200.00, NULL,
     60, NULL, 0, 350, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Spa & Massage' LIMIT 1),
     'massage-with-body-scrub-1-hour', 'Massage with Body Scrub - 1 Hour', NULL, 1200.00, NULL,
     60, NULL, 0, 360, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Spa & Massage' LIMIT 1),
     'back-massage-30-minutes', 'Back Massage - 30 Minutes', NULL, 350.00, NULL,
     30, NULL, 0, 370, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Spa & Massage' LIMIT 1),
     'hand-massage-30-minutes', 'Hand Massage - 30 Minutes', NULL, 250.00, NULL,
     30, NULL, 0, 380, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Spa & Massage' LIMIT 1),
     'foot-massage-30-minutes', 'Foot Massage - 30 Minutes', NULL, 300.00, NULL,
     30, NULL, 0, 390, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Spa & Massage' LIMIT 1),
     'hand-reflexology-30-minutes', 'Hand Reflexology - 30 Minutes', NULL, 300.00, NULL,
     30, NULL, 0, 400, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Spa & Massage' LIMIT 1),
     'foot-reflexology-30-minutes', 'Foot Reflexology - 30 Minutes', NULL, 350.00, NULL,
     30, NULL, 0, 410, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Lashes & Brows' LIMIT 1),
     'threading', 'Threading', NULL, 100.00, NULL,
     30, NULL, 0, 420, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Lashes & Brows' LIMIT 1),
     'eyelash-lift', 'Eyelash Lift', NULL, 500.00, NULL,
     60, NULL, 0, 430, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Lashes & Brows' LIMIT 1),
     'eyelash-lift-with-tint', 'Eyelash Lift with Tint', NULL, 600.00, NULL,
     75, NULL, 0, 440, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Lashes & Brows' LIMIT 1),
     'eyelash-extension-removal', 'Eyelash Extension Removal', NULL, 100.00, '₱100–₱150',
     45, NULL, 0, 450, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Lashes & Brows' LIMIT 1),
     'brow-lamination', 'Brow Lamination', NULL, 500.00, NULL,
     60, NULL, 0, 460, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-1', 'Package #1', 'Manicure + Pedicure + Foot Spa', 500.00, 'Additional ₱50 for men',
     120, NULL, 0, 470, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-2', 'Package #2', 'Manicure + Pedicure + Foot Spa + Whitening Foot Mask', 580.00, 'Additional ₱50 for men',
     120, NULL, 0, 480, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-3', 'Package #3', 'Manicure + Pedicure + Foot Spa + Threading', 580.00, 'Additional ₱50 for men',
     120, NULL, 0, 490, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-4', 'Package #4', 'Manicure + Pedicure + Foot Spa + Ear Candling', 680.00, 'Additional ₱50 for men',
     120, NULL, 0, 500, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-5', 'Package #5', 'Manicure + Pedicure + Foot Spa + Underarm Wax', 730.00, 'Additional ₱50 for men',
     120, NULL, 0, 510, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-6', 'Package #6', 'Manicure + Pedicure + Foot Spa + Hand Scrub', 730.00, 'Additional ₱50 for men',
     120, NULL, 0, 520, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-7', 'Package #7', 'Manicure + Pedicure + Foot Spa + Hand Massage (30 minutes)', 730.00, 'Additional ₱50 for men',
     120, NULL, 0, 530, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-8', 'Package #8', 'Manicure + Pedicure + Foot Spa + Foot Massage (30 minutes)', 780.00, 'Additional ₱50 for men',
     120, NULL, 0, 540, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-9', 'Package #9', 'Manicure + Pedicure + Foot Spa + Regular Facial', 980.00, 'Additional ₱50 for men',
     120, NULL, 0, 550, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-10', 'Package #10', 'Manicure + Pedicure + Foot Spa + Lash Lift', 980.00, 'Additional ₱50 for men',
     120, NULL, 0, 560, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-11', 'Package #11', 'Manicure + Pedicure + Foot Spa + Regular Massage', 980.00, 'Additional ₱50 for men',
     120, NULL, 0, 570, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-12', 'Package #12', 'Manicure + Pedicure + Foot Spa + Brow Lamination', 980.00, 'Additional ₱50 for men',
     120, NULL, 0, 580, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-13', 'Package #13', 'Gel Manicure + Regular Pedicure', 450.00, 'Additional ₱50 for men',
     120, NULL, 0, 590, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-14', 'Package #14', 'Gel Manicure + Regular Pedicure + Foot Spa', 730.00, 'Additional ₱50 for men',
     120, NULL, 0, 600, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-15', 'Package #15', 'Gel Manicure + Gel Pedicure', 730.00, 'Additional ₱50 for men',
     120, NULL, 0, 610, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-16', 'Package #16', 'Gel Manicure + Gel Pedicure + Foot Spa', 1000.00, 'Additional ₱50 for men',
     120, NULL, 0, 620, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-17', 'Package #17', 'Gel Manicure + Gel Pedicure + Foot Spa + Whitening Mask', 1080.00, 'Additional ₱50 for men',
     120, NULL, 0, 630, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-18', 'Package #18', 'Softgel Extension + Gel Polish + Pedicure', 900.00, 'Additional ₱50 for men',
     120, NULL, 0, 640, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-19', 'Package #19', 'Softgel Extension + Gel Polish + Pedicure + Foot Spa', 1200.00, 'Additional ₱50 for men',
     120, NULL, 0, 650, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Packages' LIMIT 1),
     'package-20', 'Package #20', 'Softgel Extension + Gel Polish + Gel Pedicure + Foot Spa', 1450.00, 'Additional ₱50 for men',
     120, NULL, 0, 660, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Facial & Skin' LIMIT 1),
     '12d-hifu', '12D HIFU', 'High Frequency Focused Ultrasound', 4500.00, NULL,
     90, NULL, 0, 670, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Body Contouring' LIMIT 1),
     'emsculpt-30-minutes', 'EMSculpt - 30 Minutes', NULL, 1000.00, NULL,
     30, NULL, 0, 680, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Body Contouring' LIMIT 1),
     'rf-face', 'RF Face', NULL, 350.00, NULL,
     30, NULL, 0, 690, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Body Contouring' LIMIT 1),
     'rf-arms', 'RF Arms', NULL, 500.00, NULL,
     30, NULL, 0, 700, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Body Contouring' LIMIT 1),
     'rf-legs', 'RF Legs', NULL, 600.00, NULL,
     30, NULL, 0, 710, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Body Contouring' LIMIT 1),
     'rf-tummy', 'RF Tummy', NULL, 600.00, NULL,
     30, NULL, 0, 720, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Body Contouring' LIMIT 1),
     'rf-thigh', 'RF Thigh', NULL, 800.00, NULL,
     30, NULL, 0, 730, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Body Contouring' LIMIT 1),
     'mesolipo-rf-double-chin', 'Mesolipo + RF - Double Chin', NULL, 1500.00, 'Get 5 sessions + 1 free; payable in installment',
     60, NULL, 0, 740, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Body Contouring' LIMIT 1),
     'mesolipo-rf-face', 'Mesolipo + RF - Face', NULL, 1700.00, 'Get 5 sessions + 1 free; payable in installment',
     60, NULL, 0, 750, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Body Contouring' LIMIT 1),
     'mesolipo-rf-bra-line', 'Mesolipo + RF - Bra Line', NULL, 2000.00, 'Get 5 sessions + 1 free; payable in installment',
     60, NULL, 0, 760, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Body Contouring' LIMIT 1),
     'mesolipo-rf-arms', 'Mesolipo + RF - Arms', NULL, 2500.00, 'Get 5 sessions + 1 free; payable in installment',
     60, NULL, 0, 770, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Body Contouring' LIMIT 1),
     'mesolipo-rf-love-handle', 'Mesolipo + RF - Love Handle', NULL, 2500.00, 'Get 5 sessions + 1 free; payable in installment',
     60, NULL, 0, 780, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Body Contouring' LIMIT 1),
     'mesolipo-rf-thigh', 'Mesolipo + RF - Thigh', NULL, 2500.00, 'Get 5 sessions + 1 free; payable in installment',
     60, NULL, 0, 790, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Body Contouring' LIMIT 1),
     'mesolipo-rf-tummy', 'Mesolipo + RF - Tummy', NULL, 2700.00, 'Get 5 sessions + 1 free; payable in installment',
     60, NULL, 0, 800, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Injectables' LIMIT 1),
     'lip-filler', 'Lip Filler', NULL, 8000.00, '1 mL / 1 syringe hyaluronic acid filler',
     60, NULL, 0, 810, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Injectables' LIMIT 1),
     'chin-filler', 'Chin Filler', NULL, 8000.00, '1 mL / 1 syringe hyaluronic acid filler',
     60, NULL, 0, 820, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Injectables' LIMIT 1),
     'jaw-filler', 'Jaw Filler', NULL, 15000.00, '2 mL / 2 syringes hyaluronic acid filler',
     75, NULL, 0, 830, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Injectables' LIMIT 1),
     'botox-barbie-arms', 'Botox - Barbie Arms', NULL, 7000.00, NULL,
     60, NULL, 0, 840, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Injectables' LIMIT 1),
     'botox-frown-lines', 'Botox - Frown Lines', NULL, 5000.00, NULL,
     60, NULL, 0, 850, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Injectables' LIMIT 1),
     'botox-crow-s-feet', 'Botox - Crow''s Feet', NULL, 3500.00, NULL,
     60, NULL, 0, 860, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Injectables' LIMIT 1),
     'botox-whole-forehead', 'Botox - Whole Forehead', NULL, 6500.00, NULL,
     60, NULL, 0, 870, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Injectables' LIMIT 1),
     'botox-laugh-line', 'Botox - Laugh Line', NULL, 5000.00, NULL,
     60, NULL, 0, 880, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Injectables' LIMIT 1),
     'botox-jawtox', 'Botox - Jawtox', NULL, 7000.00, NULL,
     60, NULL, 0, 890, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Injectables' LIMIT 1),
     'botox-alartox', 'Botox - Alartox', NULL, 3000.00, NULL,
     60, NULL, 0, 900, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Injectables' LIMIT 1),
     'botox-lip-flip', 'Botox - Lip Flip', NULL, 5000.00, NULL,
     60, NULL, 0, 910, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Injectables' LIMIT 1),
     'botox-traptox', 'Botox - Traptox', NULL, 7500.00, NULL,
     60, NULL, 0, 920, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Injectables' LIMIT 1),
     'botox-sweatox', 'Botox - Sweatox', NULL, 8000.00, NULL,
     60, NULL, 0, 930, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Injectables' LIMIT 1),
     'full-face-botox-promo', 'Full Face Botox Promo', NULL, 9000.00, NULL,
     60, NULL, 0, 940, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Injectables' LIMIT 1),
     'hiko-nose-lift-basic-or-retouch-package', 'Hiko Nose Lift - Basic or Retouch Package', '4 PCL threads, nose bridge filler, and take-home medicine', 5500.00, NULL,
     75, NULL, 0, 950, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Injectables' LIMIT 1),
     'hiko-nose-lift-standard-package', 'Hiko Nose Lift - Standard Package', '8 PCL threads, nose bridge filler, Alartox, nose tip filler, and take-home medicine', 10000.00, NULL,
     90, NULL, 0, 960, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Injectables' LIMIT 1),
     'hiko-nose-lift-advance-package', 'Hiko Nose Lift - Advance Package', 'Unlimited nose bridge threads, nose bridge fillers, Alartox, nose tip filler, and take-home medicine', 15000.00, NULL,
     105, NULL, 0, 970, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Injectables' LIMIT 1),
     'hiko-nose-lift-ultimate-package', 'Hiko Nose Lift - Ultimate Package', 'Unlimited nose bridge threads, nose bridge fillers, Alartox, nose tip filler, nose tip threads, and take-home medicine', 20000.00, NULL,
     120, NULL, 0, 980, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Facial & Skin' LIMIT 1),
     'regular-facial-with-mask', 'Regular Facial with Mask', NULL, 500.00, NULL,
     60, NULL, 0, 990, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Facial & Skin' LIMIT 1),
     'anti-aging-24k-gold-mask', 'Anti-Aging 24K Gold Mask', NULL, 650.00, NULL,
     60, NULL, 0, 1000, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Facial & Skin' LIMIT 1),
     'diamond-peel', 'Diamond Peel', NULL, 700.00, NULL,
     60, NULL, 0, 1010, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Facial & Skin' LIMIT 1),
     'regular-facial-with-rf-face', 'Regular Facial with RF Face', NULL, 800.00, NULL,
     60, NULL, 0, 1020, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Facial & Skin' LIMIT 1),
     'supreme-diamond-peel', 'Supreme Diamond Peel', NULL, 850.00, NULL,
     60, NULL, 0, 1030, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Facial & Skin' LIMIT 1),
     'melasma-laser-removal', 'Melasma Laser Removal', NULL, 1000.00, NULL,
     60, NULL, 0, 1040, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Facial & Skin' LIMIT 1),
     'korean-bb-glow', 'Korean BB Glow', NULL, 1200.00, NULL,
     60, NULL, 0, 1050, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Facial & Skin' LIMIT 1),
     'blackdoll-carbon-laser', 'Blackdoll Carbon Laser', NULL, 1200.00, NULL,
     60, NULL, 0, 1060, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Facial & Skin' LIMIT 1),
     'supreme-blackdoll-laser', 'Supreme Blackdoll Laser', NULL, 1500.00, NULL,
     60, NULL, 0, 1070, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Facial & Skin' LIMIT 1),
     'hydra-3-in-1-facial', 'Hydra 3-in-1 Facial', NULL, 1500.00, NULL,
     60, NULL, 0, 1080, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Facial & Skin' LIMIT 1),
     'ultimate-hydra-10-in-1-facial', 'Ultimate Hydra 10-in-1 Facial', NULL, 2000.00, NULL,
     60, NULL, 0, 1090, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Facial & Skin' LIMIT 1),
     'ultimate-acne-treatment-sqt-resurfacing', 'Ultimate Acne Treatment - SQT Resurfacing', NULL, 3500.00, NULL,
     60, NULL, 0, 1100, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Facial & Skin' LIMIT 1),
     'ultimate-melasma-treatment-sqt-revitalizing', 'Ultimate Melasma Treatment - SQT Revitalizing', NULL, 4000.00, NULL,
     60, NULL, 0, 1110, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Laser & Skin Treatments' LIMIT 1),
     'underarm-carbon-laser', 'Underarm Carbon Laser', NULL, 700.00, 'Get 5 sessions + 1 free; payable in installment',
     45, NULL, 0, 1120, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Laser & Skin Treatments' LIMIT 1),
     'bikini-carbon-laser', 'Bikini Carbon Laser', NULL, 800.00, 'Get 5 sessions + 1 free; payable in installment',
     45, NULL, 0, 1130, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Laser & Skin Treatments' LIMIT 1),
     'bikini-diode-with-carbon', 'Bikini Diode with Carbon', NULL, 1500.00, 'Get 5 sessions + 1 free; payable in installment',
     45, NULL, 0, 1140, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Laser & Skin Treatments' LIMIT 1),
     'underarm-diode-with-carbon', 'Underarm Diode with Carbon', NULL, 1300.00, 'Get 5 sessions + 1 free; payable in installment',
     45, NULL, 0, 1150, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Laser & Skin Treatments' LIMIT 1),
     'glutathione-drip', 'Glutathione Drip', NULL, 1500.00, 'Get 5 sessions + 1 free; payable in installment',
     60, NULL, 0, 1160, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Waxing' LIMIT 1),
     'waxing-fingers', 'Waxing - Fingers', NULL, 200.00, 'Add ₱50 for men',
     30, NULL, 0, 1170, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Waxing' LIMIT 1),
     'waxing-toes', 'Waxing - Toes', NULL, 200.00, 'Add ₱50 for men',
     30, NULL, 0, 1180, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Waxing' LIMIT 1),
     'waxing-upper-lip', 'Waxing - Upper Lip', NULL, 200.00, 'Add ₱50 for men',
     30, NULL, 0, 1190, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Waxing' LIMIT 1),
     'waxing-lower-lip', 'Waxing - Lower Lip', NULL, 200.00, 'Add ₱50 for men',
     30, NULL, 0, 1200, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Waxing' LIMIT 1),
     'waxing-underarm', 'Waxing - Underarm', NULL, 250.00, 'Add ₱50 for men',
     30, NULL, 0, 1210, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Waxing' LIMIT 1),
     'waxing-full-arms', 'Waxing - Full Arms', NULL, 400.00, 'Add ₱50 for men',
     30, NULL, 0, 1220, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Waxing' LIMIT 1),
     'waxing-half-legs', 'Waxing - Half Legs', NULL, 450.00, 'Add ₱50 for men',
     30, NULL, 0, 1230, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Waxing' LIMIT 1),
     'waxing-full-legs', 'Waxing - Full Legs', NULL, 650.00, 'Add ₱50 for men',
     30, NULL, 0, 1240, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Waxing' LIMIT 1),
     'waxing-bikini', 'Waxing - Bikini', NULL, 700.00, 'Add ₱50 for men',
     30, NULL, 0, 1250, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Laser & Skin Treatments' LIMIT 1),
     'diode-hair-removal-fingers', 'Diode Hair Removal - Fingers', NULL, 500.00, 'Get 5 sessions + 1 free; payable in installment',
     45, NULL, 0, 1260, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Laser & Skin Treatments' LIMIT 1),
     'diode-hair-removal-toes', 'Diode Hair Removal - Toes', NULL, 500.00, 'Get 5 sessions + 1 free; payable in installment',
     45, NULL, 0, 1270, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Laser & Skin Treatments' LIMIT 1),
     'diode-hair-removal-upper-lip', 'Diode Hair Removal - Upper Lip', NULL, 500.00, 'Get 5 sessions + 1 free; payable in installment',
     45, NULL, 0, 1280, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Laser & Skin Treatments' LIMIT 1),
     'diode-hair-removal-lower-lip', 'Diode Hair Removal - Lower Lip', NULL, 500.00, 'Get 5 sessions + 1 free; payable in installment',
     45, NULL, 0, 1290, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Laser & Skin Treatments' LIMIT 1),
     'diode-hair-removal-underarm', 'Diode Hair Removal - Underarm', NULL, 700.00, 'Get 5 sessions + 1 free; payable in installment',
     45, NULL, 0, 1300, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Laser & Skin Treatments' LIMIT 1),
     'diode-hair-removal-cheeks', 'Diode Hair Removal - Cheeks', NULL, 700.00, 'Get 5 sessions + 1 free; payable in installment',
     45, NULL, 0, 1310, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Laser & Skin Treatments' LIMIT 1),
     'diode-hair-removal-bikini', 'Diode Hair Removal - Bikini', NULL, 800.00, 'Get 5 sessions + 1 free; payable in installment',
     45, NULL, 0, 1320, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Laser & Skin Treatments' LIMIT 1),
     'diode-hair-removal-full-arms', 'Diode Hair Removal - Full Arms', NULL, 1100.00, 'Get 5 sessions + 1 free; payable in installment',
     45, NULL, 0, 1330, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Laser & Skin Treatments' LIMIT 1),
     'diode-hair-removal-half-legs', 'Diode Hair Removal - Half Legs', NULL, 1300.00, 'Get 5 sessions + 1 free; payable in installment',
     45, NULL, 0, 1340, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Laser & Skin Treatments' LIMIT 1),
     'diode-hair-removal-full-legs', 'Diode Hair Removal - Full Legs', NULL, 1500.00, 'Get 5 sessions + 1 free; payable in installment',
     45, NULL, 0, 1350, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Laser & Skin Treatments' LIMIT 1),
     'wart-or-skin-tag-removal', 'Wart or Skin Tag Removal', NULL, 800.00, '₱800–₱2,500; free consultation',
     60, NULL, 0, 1360, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Laser & Skin Treatments' LIMIT 1),
     'body-tattoo-removal', 'Body Tattoo Removal', NULL, 1000.00, '₱1,000–₱2,500; free consultation',
     60, NULL, 0, 1370, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'hair-cut', 'Hair Cut', NULL, 150.00, NULL,
     45, NULL, 0, 1380, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'blower', 'Blower', NULL, 100.00, NULL,
     30, NULL, 0, 1390, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'shampoo', 'Shampoo', NULL, 100.00, NULL,
     30, NULL, 0, 1400, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'hair-iron', 'Hair Iron', NULL, 200.00, NULL,
     45, NULL, 0, 1410, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'hair-all-in-package', 'Hair All-in Package', 'Shampoo, haircut, and blower', 350.00, NULL,
     60, NULL, 0, 1420, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'hair-color-short-hair', 'Hair Color - Short Hair', NULL, 600.00, NULL,
     120, NULL, 0, 1430, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'hair-color-medium-hair', 'Hair Color - Medium Hair', NULL, 900.00, NULL,
     120, NULL, 0, 1440, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'hair-color-long-hair', 'Hair Color - Long Hair', NULL, 1100.00, NULL,
     120, NULL, 0, 1450, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'hair-color-very-long-hair', 'Hair Color - Very Long Hair', NULL, 1300.00, NULL,
     120, NULL, 0, 1460, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'highlights-short-hair', 'Highlights - Short Hair', NULL, 600.00, NULL,
     120, NULL, 0, 1470, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'highlights-medium-hair', 'Highlights - Medium Hair', NULL, 900.00, NULL,
     120, NULL, 0, 1480, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'highlights-long-hair', 'Highlights - Long Hair', NULL, 1100.00, NULL,
     120, NULL, 0, 1490, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'highlights-very-long-hair', 'Highlights - Very Long Hair', NULL, 1300.00, NULL,
     120, NULL, 0, 1500, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'brazilian-short-hair', 'Brazilian - Short Hair', NULL, 600.00, NULL,
     120, NULL, 0, 1510, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'brazilian-medium-hair', 'Brazilian - Medium Hair', NULL, 900.00, NULL,
     120, NULL, 0, 1520, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'brazilian-long-hair', 'Brazilian - Long Hair', NULL, 1100.00, NULL,
     120, NULL, 0, 1530, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'brazilian-very-long-hair', 'Brazilian - Very Long Hair', NULL, 1300.00, '₱1,300–₱1,500 for very long hair',
     120, NULL, 0, 1540, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'hair-botox-short-hair', 'Hair Botox - Short Hair', NULL, 600.00, NULL,
     120, NULL, 0, 1550, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'hair-botox-medium-hair', 'Hair Botox - Medium Hair', NULL, 900.00, NULL,
     120, NULL, 0, 1560, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'hair-botox-long-hair', 'Hair Botox - Long Hair', NULL, 1100.00, NULL,
     120, NULL, 0, 1570, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'hair-botox-very-long-hair', 'Hair Botox - Very Long Hair', NULL, 1300.00, '₱1,300–₱1,500 for very long hair',
     120, NULL, 0, 1580, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'rebond-short-hair', 'Rebond - Short Hair', NULL, 1200.00, NULL,
     120, NULL, 0, 1590, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'rebond-medium-hair', 'Rebond - Medium Hair', NULL, 1700.00, NULL,
     120, NULL, 0, 1600, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'rebond-long-hair', 'Rebond - Long Hair', NULL, 2000.00, NULL,
     120, NULL, 0, 1610, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'rebond-very-long-hair', 'Rebond - Very Long Hair', NULL, 2200.00, '₱2,200–₱2,500 for very long hair',
     120, NULL, 0, 1620, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'hair-botox-with-color-short-hair', 'Hair Botox with Color - Short Hair', NULL, 1100.00, NULL,
     120, NULL, 0, 1630, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'hair-botox-with-color-medium-hair', 'Hair Botox with Color - Medium Hair', NULL, 1700.00, NULL,
     120, NULL, 0, 1640, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'hair-botox-with-color-long-hair', 'Hair Botox with Color - Long Hair', NULL, 2000.00, NULL,
     120, NULL, 0, 1650, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'hair-botox-with-color-very-long-hair', 'Hair Botox with Color - Very Long Hair', NULL, 2200.00, '₱2,200–₱2,500 for very long hair',
     120, NULL, 0, 1660, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'rebond-with-color-short-hair', 'Rebond with Color - Short Hair', NULL, 1700.00, NULL,
     120, NULL, 0, 1670, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'rebond-with-color-medium-hair', 'Rebond with Color - Medium Hair', NULL, 2500.00, NULL,
     120, NULL, 0, 1680, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'rebond-with-color-long-hair', 'Rebond with Color - Long Hair', NULL, 3000.00, NULL,
     120, NULL, 0, 1690, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'rebond-with-color-very-long-hair', 'Rebond with Color - Very Long Hair', NULL, 3400.00, '₱3,400–₱4,000 for very long hair',
     120, NULL, 0, 1700, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'rebond-with-brazilian-short-hair', 'Rebond with Brazilian - Short Hair', NULL, 1700.00, NULL,
     120, NULL, 0, 1710, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'rebond-with-brazilian-medium-hair', 'Rebond with Brazilian - Medium Hair', NULL, 2500.00, NULL,
     120, NULL, 0, 1720, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'rebond-with-brazilian-long-hair', 'Rebond with Brazilian - Long Hair', NULL, 3000.00, NULL,
     120, NULL, 0, 1730, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'rebond-with-brazilian-very-long-hair', 'Rebond with Brazilian - Very Long Hair', NULL, 3400.00, '₱3,400–₱4,000 for very long hair',
     120, NULL, 0, 1740, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'rebond-with-brazilian-and-color-short-hair', 'Rebond with Brazilian and Color - Short Hair', NULL, 2300.00, NULL,
     120, NULL, 0, 1750, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'rebond-with-brazilian-and-color-medium-hair', 'Rebond with Brazilian and Color - Medium Hair', NULL, 3300.00, NULL,
     120, NULL, 0, 1760, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'rebond-with-brazilian-and-color-long-hair', 'Rebond with Brazilian and Color - Long Hair', NULL, 4000.00, NULL,
     120, NULL, 0, 1770, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Hair Services' LIMIT 1),
     'rebond-with-brazilian-and-color-very-long-hair', 'Rebond with Brazilian and Color - Very Long Hair', NULL, 4200.00, '₱4,200–₱4,500 for very long hair',
     120, NULL, 0, 1780, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'ombre-brows-first-session', 'Ombre Brows - First Session', NULL, 5500.00, NULL,
     120, NULL, 0, 1790, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'ombre-brows-second-session', 'Ombre Brows - Second Session', NULL, 2500.00, 'Second session: 1–3 months after first',
     90, NULL, 0, 1800, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'nano-combo-brows-first-session', 'Nano Combo Brows - First Session', NULL, 6500.00, NULL,
     120, NULL, 0, 1810, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'nano-combo-brows-second-session', 'Nano Combo Brows - Second Session', NULL, 3500.00, 'Second session: 1–3 months after first',
     90, NULL, 0, 1820, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'nano-machine-strokes-first-session', 'Nano Machine Strokes - First Session', NULL, 7900.00, NULL,
     120, NULL, 0, 1830, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'nano-machine-strokes-second-session', 'Nano Machine Strokes - Second Session', NULL, 3500.00, 'Second session: 2 weeks–2 months after first',
     90, NULL, 0, 1840, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'menstrokes-first-session', 'Menstrokes - First Session', NULL, 7900.00, NULL,
     120, NULL, 0, 1850, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'menstrokes-second-session', 'Menstrokes - Second Session', NULL, 3500.00, 'Second session: 2 weeks–2 months after first',
     90, NULL, 0, 1860, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'lip-micropigmentation-first-session', 'Lip Micropigmentation - First Session', NULL, 6500.00, NULL,
     120, NULL, 0, 1870, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'lip-micropigmentation-second-session', 'Lip Micropigmentation - Second Session', NULL, 3500.00, 'Second session: 45 days–3 months after first',
     90, NULL, 0, 1880, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'classic-or-winged-eyeliner-tattoo-first-session', 'Classic or Winged Eyeliner Tattoo - First Session', NULL, 3500.00, '₱3,500–₱4,000',
     120, NULL, 0, 1890, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'classic-or-winged-eyeliner-tattoo-second-session', 'Classic or Winged Eyeliner Tattoo - Second Session', NULL, 1500.00, '₱1,500–₱1,800; 1–3 months after first session',
     90, NULL, 0, 1900, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'brow-reconstruction-cover-up-charge', 'Brow Reconstruction - Cover-Up Charge', NULL, 500.00, NULL,
     30, NULL, 0, 1910, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'brow-reconstruction-color-correction', 'Brow Reconstruction - Color Correction', NULL, 700.00, NULL,
     45, NULL, 0, 1920, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'brow-reconstruction-camouflage', 'Brow Reconstruction - Camouflage', NULL, 700.00, NULL,
     45, NULL, 0, 1930, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'regular-laser-brow-removal', 'Regular Laser Brow Removal', NULL, 1500.00, 'Next session after 2–3 weeks if needed',
     60, NULL, 0, 1940, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'same-day-laser-brow-package', 'Same-Day Laser Brow Package', 'Includes Ombre Brows, cover-up charge, color correction, camouflage, and reshaping', 10000.00, NULL,
     180, NULL, 0, 1950, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'ombre-brows-maintenance-1-year-onwards', 'Ombre Brows Maintenance - 1 Year Onwards', NULL, 3500.00, 'For existing clients only',
     90, NULL, 0, 1960, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'ombre-brows-maintenance-2-years-onwards', 'Ombre Brows Maintenance - 2 Years Onwards', NULL, 4000.00, 'For existing clients only',
     90, NULL, 0, 1970, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'ombre-brows-maintenance-3-years-onwards', 'Ombre Brows Maintenance - 3 Years Onwards', NULL, 4500.00, 'For existing clients only',
     90, NULL, 0, 1980, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'nano-combo-brows-maintenance-1-year-onwards', 'Nano Combo Brows Maintenance - 1 Year Onwards', NULL, 4500.00, 'For existing clients only',
     90, NULL, 0, 1990, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'nano-combo-brows-maintenance-2-years-onwards', 'Nano Combo Brows Maintenance - 2 Years Onwards', NULL, 5000.00, 'For existing clients only',
     90, NULL, 0, 2000, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'nano-combo-brows-maintenance-3-years-onwards', 'Nano Combo Brows Maintenance - 3 Years Onwards', NULL, 5500.00, 'For existing clients only',
     90, NULL, 0, 2010, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'nano-machine-strokes-maintenance-1-year-onwards', 'Nano Machine Strokes Maintenance - 1 Year Onwards', NULL, 5900.00, 'For existing clients only',
     90, NULL, 0, 2020, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'nano-machine-strokes-maintenance-2-years-onwards', 'Nano Machine Strokes Maintenance - 2 Years Onwards', NULL, 6500.00, 'For existing clients only',
     90, NULL, 0, 2030, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'nano-machine-strokes-maintenance-3-years-onwards', 'Nano Machine Strokes Maintenance - 3 Years Onwards', NULL, 6900.00, 'For existing clients only',
     90, NULL, 0, 2040, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'menstrokes-maintenance-1-year-onwards', 'Menstrokes Maintenance - 1 Year Onwards', NULL, 5900.00, 'For existing clients only',
     90, NULL, 0, 2050, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'menstrokes-maintenance-2-years-onwards', 'Menstrokes Maintenance - 2 Years Onwards', NULL, 6500.00, 'For existing clients only',
     90, NULL, 0, 2060, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'menstrokes-maintenance-3-years-onwards', 'Menstrokes Maintenance - 3 Years Onwards', NULL, 6900.00, 'For existing clients only',
     90, NULL, 0, 2070, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'lip-micropigmentation-maintenance-1-year-onwards', 'Lip Micropigmentation Maintenance - 1 Year Onwards', NULL, 4500.00, 'For existing clients only',
     90, NULL, 0, 2080, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'lip-micropigmentation-maintenance-2-years-onwards', 'Lip Micropigmentation Maintenance - 2 Years Onwards', NULL, 5000.00, 'For existing clients only',
     90, NULL, 0, 2090, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'lip-micropigmentation-maintenance-3-years-onwards', 'Lip Micropigmentation Maintenance - 3 Years Onwards', NULL, 5500.00, 'For existing clients only',
     90, NULL, 0, 2100, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'eyeliner-tattoo-maintenance-1-year-onwards', 'Eyeliner Tattoo Maintenance - 1 Year Onwards', NULL, 2000.00, '₱2,000–₱2,500; for existing clients only',
     90, NULL, 0, 2110, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'eyeliner-tattoo-maintenance-2-years-onwards', 'Eyeliner Tattoo Maintenance - 2 Years Onwards', NULL, 2500.00, '₱2,500–₱3,000; for existing clients only',
     90, NULL, 0, 2120, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
INSERT INTO `services`
    (`category_id`, `slug`, `name`, `description`, `price`,
     `price_note`, `duration`, `image`, `featured`,
     `display_order`, `status`)
VALUES
    ((SELECT `id` FROM `service_categories` WHERE `name` = 'Semi-Permanent Makeup' LIMIT 1),
     'eyeliner-tattoo-maintenance-3-years-onwards', 'Eyeliner Tattoo Maintenance - 3 Years Onwards', NULL, 3000.00, '₱3,000–₱3,500; for existing clients only',
     90, NULL, 0, 2130, 'active')
ON DUPLICATE KEY UPDATE
    `category_id` = VALUES(`category_id`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `price_note` = VALUES(`price_note`),
    `duration` = VALUES(`duration`),
    `display_order` = VALUES(`display_order`),
    `status` = 'active';
COMMIT;

-- Verification summary
SELECT COUNT(*) AS imported_service_count FROM `services`;
SELECT c.name AS category, COUNT(*) AS services FROM `services` s LEFT JOIN `service_categories` c ON c.id = s.category_id GROUP BY c.id, c.name ORDER BY c.display_order;
