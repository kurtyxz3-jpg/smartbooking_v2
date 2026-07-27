USE smartbooking_v2;

CREATE TABLE IF NOT EXISTS branches (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    branch_code VARCHAR(30) NOT NULL,
    branch_name VARCHAR(150) NOT NULL,
    address VARCHAR(255) DEFAULT NULL,
    contact_number VARCHAR(40) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    opening_time TIME DEFAULT '09:00:00',
    closing_time TIME DEFAULT '20:00:00',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_branches_code (branch_code),
    KEY idx_branches_status_order (status, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO branches (branch_code, branch_name, display_order)
VALUES
    ('BR-001', 'Main Branch', 1),
    ('BR-002', 'Branch 2', 2),
    ('BR-003', 'Branch 3', 3)
ON DUPLICATE KEY UPDATE branch_name = VALUES(branch_name);

DELIMITER $$
DROP PROCEDURE IF EXISTS sb_add_branch_column $$
CREATE PROCEDURE sb_add_branch_column(IN table_name_value VARCHAR(64))
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = DATABASE() AND table_name = table_name_value
    ) AND NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = table_name_value
          AND column_name = 'branch_id'
    ) THEN
        SET @sql_text = CONCAT(
            'ALTER TABLE `', table_name_value,
            '` ADD COLUMN branch_id INT UNSIGNED NULL, ',
            'ADD INDEX `idx_', table_name_value, '_branch` (branch_id)'
        );
        PREPARE branch_statement FROM @sql_text;
        EXECUTE branch_statement;
        DEALLOCATE PREPARE branch_statement;
    END IF;
END $$
DELIMITER ;

CALL sb_add_branch_column('users');
CALL sb_add_branch_column('bookings');
CALL sb_add_branch_column('inventory');
CALL sb_add_branch_column('promotions');
DROP PROCEDURE IF EXISTS sb_add_branch_column;

-- Preserve all existing records by assigning them to the seeded Main Branch.
SET @main_branch_id = (SELECT id FROM branches WHERE branch_code = 'BR-001' LIMIT 1);
UPDATE users SET branch_id = @main_branch_id
WHERE branch_id IS NULL AND role IN ('admin','staff');
UPDATE bookings SET branch_id = @main_branch_id WHERE branch_id IS NULL;
UPDATE inventory SET branch_id = @main_branch_id WHERE branch_id IS NULL;
UPDATE promotions SET branch_id = @main_branch_id WHERE branch_id IS NULL;

