USE smartbooking_v2;

CREATE TABLE IF NOT EXISTS registration_security_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(40) NOT NULL,
    identity_hash CHAR(64) DEFAULT NULL,
    ip_hash CHAR(64) NOT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_registration_event_ip (event_type, ip_hash, created_at),
    INDEX idx_registration_event_identity (event_type, identity_hash, created_at),
    INDEX idx_registration_event_user (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_verification_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    verification_code_hash CHAR(64) DEFAULT NULL,
    attempt_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_verification_user (user_id),
    INDEX idx_email_verification_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
