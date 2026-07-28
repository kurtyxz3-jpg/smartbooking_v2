SET NAMES utf8mb4;
SET time_zone = '+08:00';

CREATE TABLE IF NOT EXISTS ai_settings (
    id INT NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NULL,
    is_secret TINYINT(1) NOT NULL DEFAULT 0,
    updated_by INT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ai_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO ai_settings (setting_key, setting_value, is_secret) VALUES
('ai_enabled', '1', 0),
('nail_generator_enabled', '1', 0),
('ai_service_url', 'http://127.0.0.1:5000', 0),
('daily_customer_limit', '10', 0),
('images_per_request', '3', 0),
('request_timeout_seconds', '300', 0),
('image_model', 'gpt-image-1', 0)
ON DUPLICATE KEY UPDATE setting_key = VALUES(setting_key);

CREATE TABLE IF NOT EXISTS ai_design_requests (
    id INT NOT NULL AUTO_INCREMENT,
    customer_id INT NOT NULL,
    provider VARCHAR(40) NOT NULL DEFAULT 'demo',
    model_name VARCHAR(100) NULL,
    nail_shape VARCHAR(40) NOT NULL,
    nail_length VARCHAR(40) NOT NULL,
    colors VARCHAR(180) NOT NULL,
    design_style VARCHAR(100) NOT NULL,
    occasion VARCHAR(100) NULL,
    add_ons JSON NULL,
    instructions VARCHAR(600) NULL,
    prompt_text TEXT NULL,
    status ENUM('queued','processing','completed','failed')
        NOT NULL DEFAULT 'queued',
    error_message TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_ai_request_customer_date (customer_id, created_at),
    KEY idx_ai_request_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_generated_designs (
    id INT NOT NULL AUTO_INCREMENT,
    request_id INT NOT NULL,
    customer_id INT NOT NULL,
    design_number TINYINT UNSIGNED NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    is_selected TINYINT(1) NOT NULL DEFAULT 0,
    is_saved TINYINT(1) NOT NULL DEFAULT 1,
    selected_at DATETIME NULL,
    deleted_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ai_request_number (request_id, design_number),
    KEY idx_ai_design_customer (customer_id),
    KEY idx_ai_design_request (request_id),
    KEY idx_ai_design_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

SET @add_selected_at := IF(
    NOT EXISTS(
        SELECT 1 FROM information_schema.columns
        WHERE table_schema=DATABASE() AND table_name='ai_generated_designs'
          AND column_name='selected_at'
    ),
    'ALTER TABLE ai_generated_designs ADD COLUMN selected_at DATETIME NULL',
    'SELECT 1'
);
PREPARE stmt FROM @add_selected_at; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @add_deleted_at := IF(
    NOT EXISTS(
        SELECT 1 FROM information_schema.columns
        WHERE table_schema=DATABASE() AND table_name='ai_generated_designs'
          AND column_name='deleted_at'
    ),
    'ALTER TABLE ai_generated_designs ADD COLUMN deleted_at DATETIME NULL',
    'SELECT 1'
);
PREPARE stmt FROM @add_deleted_at; EXECUTE stmt; DEALLOCATE PREPARE stmt;

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

-- Foreign keys are intentionally omitted because earlier SmartBooking
-- installations used mixed signed and unsigned identifier columns.
