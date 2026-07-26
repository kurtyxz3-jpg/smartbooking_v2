SET NAMES utf8mb4;
SET time_zone = '+08:00';

INSERT INTO ai_settings (setting_key, setting_value, is_secret) VALUES
('lip_preview_enabled', '1', 0),
('eyebrow_preview_enabled', '1', 0),
('beauty_photo_retention_hours', '24', 0)
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

CREATE TABLE IF NOT EXISTS ai_beauty_requests (
    id BIGINT NOT NULL AUTO_INCREMENT,
    customer_id INT NOT NULL,
    beauty_area ENUM('lips','eyebrows') NOT NULL,
    color_value VARCHAR(100) NOT NULL,
    service_style VARCHAR(100) NULL,
    finish_type VARCHAR(60) NULL,
    brow_shape VARCHAR(60) NULL,
    thickness VARCHAR(40) NULL,
    instructions VARCHAR(600) NULL,
    used_customer_photo TINYINT(1) NOT NULL DEFAULT 0,
    consent_given TINYINT(1) NOT NULL DEFAULT 0,
    provider VARCHAR(40) NOT NULL DEFAULT 'demo',
    model_name VARCHAR(100) NULL,
    status ENUM('processing','completed','failed') NOT NULL DEFAULT 'processing',
    error_message TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_beauty_request_customer (customer_id, created_at),
    KEY idx_beauty_request_area (beauty_area, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_beauty_results (
    id BIGINT NOT NULL AUTO_INCREMENT,
    request_id BIGINT NOT NULL,
    customer_id INT NOT NULL,
    result_number TINYINT NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    is_selected TINYINT(1) NOT NULL DEFAULT 0,
    is_saved TINYINT(1) NOT NULL DEFAULT 1,
    selected_at DATETIME NULL,
    deleted_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_beauty_request_result (request_id, result_number),
    KEY idx_beauty_result_customer (customer_id, deleted_at, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @add_booking_beauty := IF(
    EXISTS(
        SELECT 1 FROM information_schema.tables
        WHERE table_schema=DATABASE() AND table_name='bookings'
    ) AND NOT EXISTS(
        SELECT 1 FROM information_schema.columns
        WHERE table_schema=DATABASE() AND table_name='bookings'
          AND column_name='selected_beauty_result_id'
    ),
    'ALTER TABLE bookings ADD COLUMN selected_beauty_result_id BIGINT NULL, ADD KEY idx_booking_beauty_result (selected_beauty_result_id)',
    'SELECT 1'
);
PREPARE stmt FROM @add_booking_beauty; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Foreign keys are omitted for compatibility with earlier signed/unsigned IDs.

