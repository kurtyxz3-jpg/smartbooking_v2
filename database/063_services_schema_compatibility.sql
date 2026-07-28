-- SmartBooking V2
-- Services module schema compatibility migration
-- Safe to import more than once on MariaDB 10.4+ (the version bundled with
-- common XAMPP installations).

USE `smartbooking_v2`;

CREATE TABLE IF NOT EXISTS `service_categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(120) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `display_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_service_categories_name` (`name`),
    KEY `idx_service_categories_status_order`
        (`status`, `display_order`, `name`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Complete a service_categories table that may have been created by an
-- earlier partial fix.
ALTER TABLE `service_categories`
    ADD COLUMN IF NOT EXISTS `description`
        VARCHAR(255) DEFAULT NULL AFTER `name`,
    ADD COLUMN IF NOT EXISTS `status`
        ENUM('active', 'inactive') NOT NULL DEFAULT 'active'
        AFTER `description`,
    ADD COLUMN IF NOT EXISTS `display_order`
        INT UNSIGNED NOT NULL DEFAULT 0 AFTER `status`,
    ADD COLUMN IF NOT EXISTS `created_at`
        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        AFTER `display_order`,
    ADD COLUMN IF NOT EXISTS `updated_at`
        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- Preserve values from the earlier sort_order column when it exists.
SET @has_sort_order := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'service_categories'
      AND column_name = 'sort_order'
);

SET @copy_sort_order_sql := IF(
    @has_sort_order > 0,
    'UPDATE service_categories
     SET display_order = sort_order
     WHERE display_order = 0 AND sort_order IS NOT NULL',
    'SELECT 1'
);

PREPARE copy_sort_order_statement FROM @copy_sort_order_sql;
EXECUTE copy_sort_order_statement;
DEALLOCATE PREPARE copy_sort_order_statement;

-- Complete the existing services table without removing any current data.
ALTER TABLE `services`
    ADD COLUMN IF NOT EXISTS `category_id`
        INT UNSIGNED DEFAULT NULL AFTER `id`,
    ADD COLUMN IF NOT EXISTS `slug`
        VARCHAR(180) DEFAULT NULL AFTER `category_id`,
    ADD COLUMN IF NOT EXISTS `price_note`
        VARCHAR(160) DEFAULT NULL AFTER `price`,
    ADD COLUMN IF NOT EXISTS `image`
        VARCHAR(500) DEFAULT NULL AFTER `duration`,
    ADD COLUMN IF NOT EXISTS `featured`
        TINYINT(1) NOT NULL DEFAULT 0 AFTER `image`,
    ADD COLUMN IF NOT EXISTS `display_order`
        INT UNSIGNED NOT NULL DEFAULT 0 AFTER `featured`,
    ADD COLUMN IF NOT EXISTS `created_at`
        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `status`,
    ADD COLUMN IF NOT EXISTS `updated_at`
        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- Existing services need stable, unique slugs before the administration page
-- can search and edit them safely.
UPDATE `services`
SET `slug` = CONCAT('service-', `id`)
WHERE `slug` IS NULL OR TRIM(`slug`) = '';

-- The following indexes match the search, filter, and sort operations in
-- admin/services.php. MariaDB accepts IF NOT EXISTS for indexes.
ALTER TABLE `services`
    ADD INDEX IF NOT EXISTS `idx_services_category` (`category_id`),
    ADD INDEX IF NOT EXISTS `idx_services_slug` (`slug`),
    ADD INDEX IF NOT EXISTS `idx_services_featured` (`featured`),
    ADD INDEX IF NOT EXISTS `idx_services_display_order`
        (`display_order`, `name`);

ALTER TABLE `service_categories`
    ADD UNIQUE INDEX IF NOT EXISTS `uq_service_categories_name` (`name`),
    ADD INDEX IF NOT EXISTS `idx_service_categories_status_order`
        (`status`, `display_order`, `name`);

-- Starter categories used by the current salon catalogue. Existing rows are
-- retained because name is unique and INSERT IGNORE is non-destructive.
INSERT IGNORE INTO `service_categories`
    (`name`, `description`, `status`, `display_order`)
VALUES
    ('Nail Care', 'Manicure, pedicure, polish, extensions, and nail art.',
     'active', 10),
    ('Spa & Massage', 'Spa, scrub, and massage services.',
     'active', 20),
    ('Hair Services', 'Hair styling, color, treatment, and care.',
     'active', 30),
    ('Facial & Skin', 'Facial, skin-care, and beauty treatments.',
     'active', 40),
    ('Lashes & Brows', 'Eyelash and eyebrow services.',
     'active', 50),
    ('Semi-Permanent Makeup', 'Brows, eyeliner, and lip pigmentation.',
     'active', 60),
    ('Waxing', 'Hair-removal and waxing services.',
     'active', 70),
    ('Packages', 'Bundled salon and beauty packages.',
     'active', 80),
    ('Other Services', 'Services that do not yet have a dedicated category.',
     'active', 90);

-- Report the resulting structures in phpMyAdmin after import.
SHOW COLUMNS FROM `service_categories`;
SHOW COLUMNS FROM `services`;
