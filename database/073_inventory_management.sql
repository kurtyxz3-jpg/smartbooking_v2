USE smartbooking_v2;

CREATE TABLE IF NOT EXISTS inventory_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_inventory_category_name (category_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT DEFAULT NULL,
    supplier_name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(120) DEFAULT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_supplier_branch_status (branch_id, status),
    KEY idx_supplier_name (supplier_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Idempotently extends the existing inventory table without replacing data.
DROP PROCEDURE IF EXISTS sb_add_inventory_column;
DELIMITER $$
CREATE PROCEDURE sb_add_inventory_column(
    IN column_name_value VARCHAR(64),
    IN definition_value VARCHAR(255)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'inventory'
          AND column_name = column_name_value
    ) THEN
        SET @statement_text = CONCAT(
            'ALTER TABLE inventory ADD COLUMN `',
            REPLACE(column_name_value, '`', ''),
            '` ', definition_value
        );
        PREPARE statement_to_run FROM @statement_text;
        EXECUTE statement_to_run;
        DEALLOCATE PREPARE statement_to_run;
    END IF;
END$$
DELIMITER ;

CALL sb_add_inventory_column('branch_id', 'INT DEFAULT NULL');
CALL sb_add_inventory_column('category_id', 'INT DEFAULT NULL');
CALL sb_add_inventory_column('supplier_id', 'INT DEFAULT NULL');
CALL sb_add_inventory_column('sku', 'VARCHAR(80) DEFAULT NULL');
CALL sb_add_inventory_column('barcode', 'VARCHAR(120) DEFAULT NULL');
CALL sb_add_inventory_column('cost_price', 'DECIMAL(12,2) NOT NULL DEFAULT 0.00');
CALL sb_add_inventory_column('notes', 'VARCHAR(500) DEFAULT NULL');
CALL sb_add_inventory_column('archived_at', 'DATETIME DEFAULT NULL');
CALL sb_add_inventory_column('updated_at', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
DROP PROCEDURE sb_add_inventory_column;

-- Existing rows are assigned to the first active branch only when unassigned.
UPDATE inventory
SET branch_id = (
    SELECT id FROM branches WHERE status='active'
    ORDER BY display_order, id LIMIT 1
)
WHERE branch_id IS NULL
  AND EXISTS (SELECT 1 FROM branches WHERE status='active');

CREATE TABLE IF NOT EXISTS inventory_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inventory_id INT NOT NULL,
    branch_id INT NOT NULL,
    movement_type ENUM('in','out','adjustment') NOT NULL,
    quantity DECIMAL(12,2) NOT NULL,
    quantity_before DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    quantity_after DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    note VARCHAR(255) DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_inventory_movement_product (inventory_id, created_at),
    KEY idx_inventory_movement_branch (branch_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Preserve movement history created by earlier SmartBooking updates.
DROP PROCEDURE IF EXISTS sb_add_movement_column;
DELIMITER $$
CREATE PROCEDURE sb_add_movement_column(
    IN column_name_value VARCHAR(64),
    IN definition_value VARCHAR(255)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'inventory_movements'
          AND column_name = column_name_value
    ) THEN
        SET @movement_statement = CONCAT(
            'ALTER TABLE inventory_movements ADD COLUMN `',
            REPLACE(column_name_value, '`', ''),
            '` ', definition_value
        );
        PREPARE movement_column_to_run FROM @movement_statement;
        EXECUTE movement_column_to_run;
        DEALLOCATE PREPARE movement_column_to_run;
    END IF;
END$$
DELIMITER ;
CALL sb_add_movement_column('branch_id', 'INT DEFAULT NULL AFTER inventory_id');
CALL sb_add_movement_column('quantity_before', 'DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER quantity');
CALL sb_add_movement_column('quantity_after', 'DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER quantity_before');
DROP PROCEDURE sb_add_movement_column;

UPDATE inventory_movements m
INNER JOIN inventory i ON i.id = m.inventory_id
SET m.branch_id = i.branch_id
WHERE m.branch_id IS NULL;

-- Foreign keys are intentionally omitted because earlier SmartBooking builds
-- use a mixture of signed and unsigned integer IDs. Branch-scoped application
-- checks and indexes preserve compatibility without changing existing IDs.

INSERT IGNORE INTO inventory_categories(category_name, description) VALUES
('Nail Supplies', 'Polish, gel, acrylic, extensions, and nail-art materials'),
('Spa and Massage', 'Oils, scrubs, towels, and treatment supplies'),
('Hair Care', 'Shampoo, color, treatment, and styling products'),
('Skin Care', 'Facial and skin-treatment consumables'),
('Sanitation', 'Disinfectants, gloves, masks, and hygiene supplies'),
('General Supplies', 'Other operational products and consumables');

DROP PROCEDURE IF EXISTS sb_add_inventory_index;
DELIMITER $$
CREATE PROCEDURE sb_add_inventory_index(
    IN index_name_value VARCHAR(64),
    IN index_columns_value VARCHAR(255)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'inventory'
          AND index_name = index_name_value
    ) THEN
        SET @index_statement = CONCAT(
            'CREATE INDEX `', REPLACE(index_name_value, '`', ''),
            '` ON inventory (', index_columns_value, ')'
        );
        PREPARE index_to_run FROM @index_statement;
        EXECUTE index_to_run;
        DEALLOCATE PREPARE index_to_run;
    END IF;
END$$
DELIMITER ;
CALL sb_add_inventory_index('idx_inventory_branch_status', 'branch_id, status');
CALL sb_add_inventory_index('idx_inventory_branch_category', 'branch_id, category_id');
CALL sb_add_inventory_index('idx_inventory_barcode', 'barcode');
CALL sb_add_inventory_index('idx_inventory_sku', 'sku');
DROP PROCEDURE sb_add_inventory_index;
