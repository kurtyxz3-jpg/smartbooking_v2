USE smartbooking_v2;

CREATE TABLE IF NOT EXISTS system_settings (
    id TINYINT UNSIGNED PRIMARY KEY,
    salon_name VARCHAR(150) NOT NULL,
    contact_number VARCHAR(50) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    facebook_url VARCHAR(255) DEFAULT NULL,
    opening_time TIME DEFAULT '09:00:00',
    closing_time TIME DEFAULT '18:00:00',
    downpayment_percentage TINYINT UNSIGNED NOT NULL DEFAULT 50,
    grace_period_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 15,
    default_language ENUM('English', 'Filipino') NOT NULL DEFAULT 'English',
    timezone VARCHAR(100) NOT NULL DEFAULT 'Asia/Manila',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ai_settings (
    id TINYINT UNSIGNED PRIMARY KEY,
    nail_generator_enabled TINYINT(1) NOT NULL DEFAULT 1,
    chatbot_enabled TINYINT(1) NOT NULL DEFAULT 1,
    receipt_verification_enabled TINYINT(1) NOT NULL DEFAULT 1,
    style_recommendation_enabled TINYINT(1) NOT NULL DEFAULT 1,
    daily_generation_limit SMALLINT UNSIGNED NOT NULL DEFAULT 10,
    ai_service_url VARCHAR(255) DEFAULT 'http://127.0.0.1:5000',
    api_key VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS notification_settings (
    id TINYINT UNSIGNED PRIMARY KEY,
    sms_enabled TINYINT(1) NOT NULL DEFAULT 1,
    email_enabled TINYINT(1) NOT NULL DEFAULT 1,
    push_enabled TINYINT(1) NOT NULL DEFAULT 1,
    booking_confirmation_enabled TINYINT(1) NOT NULL DEFAULT 1,
    payment_confirmation_enabled TINYINT(1) NOT NULL DEFAULT 1,
    promotion_enabled TINYINT(1) NOT NULL DEFAULT 1,
    reminder_minutes INT UNSIGNED NOT NULL DEFAULT 30,
    sms_sender_name VARCHAR(50) NOT NULL DEFAULT 'NBW',
    reply_to_email VARCHAR(150) DEFAULT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    username VARCHAR(100) NOT NULL,
    action VARCHAR(150) NOT NULL,
    module VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_user_id (user_id),
    INDEX idx_audit_module (module),
    INDEX idx_audit_created_at (created_at)
);

INSERT INTO system_settings (
    id,
    salon_name,
    opening_time,
    closing_time,
    downpayment_percentage,
    grace_period_minutes,
    default_language,
    timezone
)
VALUES (
    1,
    'Nails & Body Worx',
    '09:00:00',
    '18:00:00',
    50,
    15,
    'English',
    'Asia/Manila'
)
ON DUPLICATE KEY UPDATE id = id;

INSERT INTO ai_settings (
    id,
    nail_generator_enabled,
    chatbot_enabled,
    receipt_verification_enabled,
    style_recommendation_enabled,
    daily_generation_limit,
    ai_service_url
)
VALUES (
    1,
    1,
    1,
    1,
    1,
    10,
    'http://127.0.0.1:5000'
)
ON DUPLICATE KEY UPDATE id = id;

INSERT INTO notification_settings (
    id,
    sms_enabled,
    email_enabled,
    push_enabled,
    booking_confirmation_enabled,
    payment_confirmation_enabled,
    promotion_enabled,
    reminder_minutes,
    sms_sender_name
)
VALUES (
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    30,
    'NBW'
)
ON DUPLICATE KEY UPDATE id = id;
