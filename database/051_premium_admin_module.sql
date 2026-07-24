USE smartbooking_v2;

-- Remove the incomplete table left by a failed import.
DROP TABLE IF EXISTS inventory_movements;

-- This compatibility version intentionally avoids foreign-key constraints.
-- Some earlier SmartBooking database versions use signed INT IDs, while
-- newer versions use INT UNSIGNED. MySQL rejects a foreign key when the
-- referenced and referencing columns do not match exactly.
CREATE TABLE inventory_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inventory_id INT NOT NULL,
    movement_type ENUM(
        'in',
        'out',
        'adjustment'
    ) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    note VARCHAR(255) DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_inventory_movement_item (
        inventory_id,
        created_at
    ),

    KEY idx_inventory_movement_user (
        created_by
    ),

    KEY idx_inventory_movement_type (
        movement_type
    )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
