USE smartbooking_v2;

DELIMITER $$
DROP PROCEDURE IF EXISTS add_promotion_popup_column$$
CREATE PROCEDURE add_promotion_popup_column(
    IN column_name_value VARCHAR(64),
    IN column_definition_value TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'promotions'
          AND COLUMN_NAME = column_name_value
    ) THEN
        SET @promotion_alter = CONCAT(
            'ALTER TABLE promotions ADD COLUMN `',
            REPLACE(column_name_value, '`', '``'),
            '` ',
            column_definition_value
        );
        PREPARE promotion_statement FROM @promotion_alter;
        EXECUTE promotion_statement;
        DEALLOCATE PREPARE promotion_statement;
    END IF;
END$$
DELIMITER ;

CALL add_promotion_popup_column('badge', "VARCHAR(100) NULL AFTER description");
CALL add_promotion_popup_column('promo_price', "DECIMAL(10,2) NULL AFTER discount_value");
CALL add_promotion_popup_column('original_price', "DECIMAL(10,2) NULL AFTER promo_price");
CALL add_promotion_popup_column('button_label', "VARCHAR(80) NOT NULL DEFAULT 'View offer' AFTER original_price");
CALL add_promotion_popup_column('button_link', "VARCHAR(500) NOT NULL DEFAULT 'customer/services.php' AFTER button_label");
CALL add_promotion_popup_column('image_path', "VARCHAR(500) NULL AFTER button_link");
CALL add_promotion_popup_column('theme', "ENUM('gold','rose','brown') NOT NULL DEFAULT 'gold' AFTER image_path");
CALL add_promotion_popup_column('show_in_popup', "TINYINT(1) NOT NULL DEFAULT 0 AFTER theme");
CALL add_promotion_popup_column('show_on_homepage', "TINYINT(1) NOT NULL DEFAULT 0 AFTER show_in_popup");
CALL add_promotion_popup_column('is_global', "TINYINT(1) NOT NULL DEFAULT 0 AFTER show_on_homepage");
CALL add_promotion_popup_column('display_order', "INT NOT NULL DEFAULT 0 AFTER is_global");

DROP PROCEDURE IF EXISTS add_promotion_popup_column;

SET @default_branch_id = (
    SELECT id FROM branches
    WHERE status = 'active'
    ORDER BY display_order, id
    LIMIT 1
);
SET @default_branch_id = COALESCE(@default_branch_id, 1);

INSERT INTO promotions (
    title, description, badge, discount_value, promo_price, original_price,
    start_date, end_date, status, button_label, button_link, image_path,
    theme, show_in_popup, show_on_homepage, is_global, display_order, branch_id
)
SELECT
    'Glow Starter Package',
    'Enjoy a manicure, pedicure, and relaxing foot spa in one introductory visit.',
    'New Guest Offer', 10.20, 449.00, 500.00,
    CURDATE(), DATE_ADD(CURDATE(), INTERVAL 90 DAY), 'active',
    'Book this offer', 'customer/book.php?service=Manicure',
    '/smartbooking_v2/assets/images/home-packages/package-foot-spa.webp',
    'gold', 1, 1, 1, 10, @default_branch_id
WHERE NOT EXISTS (
    SELECT 1 FROM promotions WHERE title = 'Glow Starter Package'
);

INSERT INTO promotions (
    title, description, badge, discount_value, promo_price, original_price,
    start_date, end_date, status, button_label, button_link, image_path,
    theme, show_in_popup, show_on_homepage, is_global, display_order, branch_id
)
SELECT
    'Gel Polish Pair',
    'Book gel polish for hands and feet from Monday to Thursday and save on your set.',
    'Weekday Beauty Treat', 13.47, 649.00, 750.00,
    CURDATE(), DATE_ADD(CURDATE(), INTERVAL 90 DAY), 'active',
    'Choose gel polish',
    'customer/book.php?service=Gel%20Polish%20-%20Hands',
    '/smartbooking_v2/assets/images/services/nails/nails-14.webp',
    'rose', 1, 1, 1, 20, @default_branch_id
WHERE NOT EXISTS (
    SELECT 1 FROM promotions WHERE title = 'Gel Polish Pair'
);

INSERT INTO promotions (
    title, description, badge, discount_value, promo_price, original_price,
    start_date, end_date, status, button_label, button_link, image_path,
    theme, show_in_popup, show_on_homepage, is_global, display_order, branch_id
)
SELECT
    'Massage + Foot Spa',
    'A full hour of massage followed by a refreshing ladies or gentlemen foot spa.',
    'Relax & Renew', 12.63, 699.00, 800.00,
    CURDATE(), DATE_ADD(CURDATE(), INTERVAL 90 DAY), 'active',
    'Reserve relaxation',
    'customer/book.php?service=Regular%20Massage%20-%201%20Hour',
    '/smartbooking_v2/assets/images/home-packages/package-massage.webp',
    'brown', 1, 1, 1, 30, @default_branch_id
WHERE NOT EXISTS (
    SELECT 1 FROM promotions WHERE title = 'Massage + Foot Spa'
);

SET @promotion_index_exists = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'promotions'
      AND INDEX_NAME = 'idx_promotions_popup_schedule'
);
SET @promotion_index_sql = IF(
    @promotion_index_exists = 0,
    'CREATE INDEX idx_promotions_popup_schedule ON promotions (show_in_popup, status, start_date, end_date, display_order)',
    'SELECT 1'
);
PREPARE promotion_index_statement FROM @promotion_index_sql;
EXECUTE promotion_index_statement;
DEALLOCATE PREPARE promotion_index_statement;
