USE smartbooking_v2;

CREATE TABLE IF NOT EXISTS customer_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) NOT NULL,
    birthday DATE DEFAULT NULL,
    gender ENUM(
        'female',
        'male',
        'non_binary',
        'prefer_not_to_say'
    ) DEFAULT NULL,
    preferred_language ENUM(
        'english',
        'filipino',
        'taglish'
    ) NOT NULL DEFAULT 'english',
    favorite_nail_shape VARCHAR(50) DEFAULT NULL,
    favorite_colors TEXT DEFAULT NULL,
    preferred_staff_id INT UNSIGNED DEFAULT NULL,
    registration_method ENUM(
        'personal_info',
        'google',
        'facebook'
    ) NOT NULL DEFAULT 'personal_info',
    email_verification_token VARCHAR(128) DEFAULT NULL,
    email_verified_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer_profile_user (user_id),
    INDEX idx_customer_profile_staff (preferred_staff_id)
);

CREATE TABLE IF NOT EXISTS social_accounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    provider ENUM('google', 'facebook') NOT NULL,
    provider_user_id VARCHAR(255) NOT NULL,
    provider_email VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_social_provider_user (
        provider,
        provider_user_id
    ),
    INDEX idx_social_user (user_id)
);
