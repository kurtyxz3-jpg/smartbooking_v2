SET NAMES utf8mb4;
SET time_zone = '+08:00';

CREATE TABLE IF NOT EXISTS ai_usage_events (
    id BIGINT NOT NULL AUTO_INCREMENT,
    customer_id INT NOT NULL,
    request_id INT NULL,
    event_type ENUM('generated','selected','downloaded','booked','deleted','failed')
        NOT NULL,
    design_id INT NULL,
    booking_id INT NULL,
    provider VARCHAR(40) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ai_usage_date (created_at),
    KEY idx_ai_usage_customer (customer_id, created_at),
    KEY idx_ai_usage_event (event_type, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_daily_limit_resets (
    id BIGINT NOT NULL AUTO_INCREMENT,
    customer_id INT NOT NULL,
    reset_by INT NOT NULL,
    reset_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reason VARCHAR(255) NULL,
    PRIMARY KEY (id),
    KEY idx_ai_limit_reset_customer (customer_id, reset_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @add_deleted_at := IF(
    EXISTS(
        SELECT 1 FROM information_schema.tables
        WHERE table_schema=DATABASE() AND table_name='ai_generated_designs'
    ) AND NOT EXISTS(
        SELECT 1 FROM information_schema.columns
        WHERE table_schema=DATABASE() AND table_name='ai_generated_designs'
          AND column_name='deleted_at'
    ),
    'ALTER TABLE ai_generated_designs ADD COLUMN deleted_at DATETIME NULL, ADD KEY idx_ai_design_deleted (deleted_at)',
    'SELECT 1'
);
PREPARE stmt FROM @add_deleted_at; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_selected_at := IF(
    EXISTS(
        SELECT 1 FROM information_schema.tables
        WHERE table_schema=DATABASE() AND table_name='ai_generated_designs'
    ) AND NOT EXISTS(
        SELECT 1 FROM information_schema.columns
        WHERE table_schema=DATABASE() AND table_name='ai_generated_designs'
          AND column_name='selected_at'
    ),
    'ALTER TABLE ai_generated_designs ADD COLUMN selected_at DATETIME NULL',
    'SELECT 1'
);
PREPARE stmt FROM @add_selected_at; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_booking_design := IF(
    EXISTS(
        SELECT 1 FROM information_schema.tables
        WHERE table_schema=DATABASE() AND table_name='bookings'
    ) AND NOT EXISTS(
        SELECT 1 FROM information_schema.columns
        WHERE table_schema=DATABASE() AND table_name='bookings'
          AND column_name='selected_ai_design_id'
    ),
    'ALTER TABLE bookings ADD COLUMN selected_ai_design_id INT NULL, ADD KEY idx_booking_ai_design (selected_ai_design_id)',
    'SELECT 1'
);
PREPARE stmt FROM @add_booking_design; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Foreign keys are intentionally omitted for compatibility with earlier
-- SmartBooking databases that used different signed/unsigned identifier types.

