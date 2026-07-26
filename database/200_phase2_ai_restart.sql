SET NAMES utf8mb4;
SET time_zone = '+08:00';

-- SmartBooking previously used a one-row/wide ai_settings table. Detect it,
-- preserve it as a backup, then install the Phase 2 key/value structure.
SET @ai_table_exists := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'ai_settings'
);

SET @ai_key_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'ai_settings'
      AND column_name = 'setting_key'
);

SET @drop_old_backup_sql := IF(
    @ai_table_exists = 1 AND @ai_key_column_exists = 0,
    'DROP TABLE IF EXISTS ai_settings_phase1_backup',
    'SELECT 1'
);
PREPARE ai_drop_backup_statement FROM @drop_old_backup_sql;
EXECUTE ai_drop_backup_statement;
DEALLOCATE PREPARE ai_drop_backup_statement;

SET @backup_legacy_sql := IF(
    @ai_table_exists = 1 AND @ai_key_column_exists = 0,
    'RENAME TABLE ai_settings TO ai_settings_phase1_backup',
    'SELECT 1'
);
PREPARE ai_backup_legacy_statement FROM @backup_legacy_sql;
EXECUTE ai_backup_legacy_statement;
DEALLOCATE PREPARE ai_backup_legacy_statement;

CREATE TABLE IF NOT EXISTS ai_settings (
    id INT NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NULL,
    is_secret TINYINT(1) NOT NULL DEFAULT 0,
    updated_by INT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ai_settings_key (setting_key),
    KEY idx_ai_settings_updated_by (updated_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO ai_settings (setting_key, setting_value, is_secret) VALUES
('ai_enabled', '1', 0),
('nail_generator_enabled', '1', 0),
('chatbot_enabled', '0', 0),
('receipt_verification_enabled', '0', 0),
('style_recommendation_enabled', '0', 0),
('ai_service_url', 'http://127.0.0.1:5000', 0),
('daily_customer_limit', '10', 0),
('images_per_request', '3', 0),
('request_timeout_seconds', '240', 0),
('image_model', 'gpt-image-2', 0)
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

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
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ai_request_number (request_id, design_number),
    KEY idx_ai_design_customer (customer_id),
    KEY idx_ai_design_request (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Foreign keys are intentionally omitted here. Earlier SmartBooking databases
-- used different signed/unsigned ID types; indexes preserve compatibility.
