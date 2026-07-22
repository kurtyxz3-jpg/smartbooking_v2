-- ============================================================
-- SmartBooking Version 2
-- Consolidated Database Schema
-- Update 030
-- ============================================================
--
-- IMPORTANT:
-- This file is intended for a NEW/FRESH database installation.
-- It creates the complete current schema used by the existing
-- Super Admin, Admin, Staff, Localization, Reporting, and
-- Customer Registration modules.
--
-- Default Super Admin login:
-- Username: SupAdmin
-- Password: SupAdmin
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+08:00';

CREATE DATABASE IF NOT EXISTS smartbooking_v2
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE smartbooking_v2;

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 1. USERS AND ACCOUNT ACCESS
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(150) NOT NULL,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    phone_number VARCHAR(30) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM(
        'super_admin',
        'admin',
        'staff',
        'customer'
    ) NOT NULL,
    status ENUM(
        'active',
        'inactive'
    ) NOT NULL DEFAULT 'active',
    profile_picture VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role_status (role, status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2. SYSTEM SETTINGS
-- ------------------------------------------------------------

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
    default_language ENUM(
        'English',
        'Filipino',
        'Taglish'
    ) NOT NULL DEFAULT 'English',
    timezone VARCHAR(100) NOT NULL DEFAULT 'Asia/Manila',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3. LOCALIZATION
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS localization_settings (
    id TINYINT UNSIGNED PRIMARY KEY,
    language_code ENUM(
        'english',
        'filipino',
        'taglish'
    ) NOT NULL DEFAULT 'english',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_language_preferences (
    user_id INT UNSIGNED PRIMARY KEY,
    language_code ENUM(
        'english',
        'filipino',
        'taglish'
    ) NOT NULL DEFAULT 'english',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_user_language_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4. STAFF AND EMPLOYMENT
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS staff_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    specialty VARCHAR(100) DEFAULT NULL,
    branch VARCHAR(150) NOT NULL DEFAULT 'Main Branch',
    employment_status ENUM(
        'full_time',
        'part_time',
        'contractual'
    ) NOT NULL DEFAULT 'full_time',
    hired_at DATE DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_staff_profiles_user (user_id),
    KEY idx_staff_profile_user (user_id),

    CONSTRAINT fk_staff_profile_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employment_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    employee_number VARCHAR(50) NOT NULL,
    date_hired DATE NOT NULL,
    employment_status ENUM(
        'full_time',
        'part_time',
        'contractual'
    ) NOT NULL DEFAULT 'full_time',
    salary_type ENUM(
        'monthly',
        'daily',
        'hourly',
        'commission'
    ) NOT NULL DEFAULT 'monthly',
    salary_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    contract_end_date DATE DEFAULT NULL,
    emergency_contact_name VARCHAR(150) DEFAULT NULL,
    emergency_contact_number VARCHAR(50) DEFAULT NULL,
    emergency_contact_relationship VARCHAR(100) DEFAULT NULL,
    government_id_type VARCHAR(100) DEFAULT NULL,
    government_id_number VARCHAR(100) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    record_status ENUM(
        'active',
        'archived'
    ) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_employment_user (user_id),
    UNIQUE KEY uq_employee_number (employee_number),
    KEY idx_employment_status (employment_status),
    KEY idx_employment_record_status (record_status),

    CONSTRAINT fk_employment_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5. CUSTOMER REGISTRATION AND SOCIAL ACCOUNTS
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS customer_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
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

    UNIQUE KEY uq_customer_profiles_user (user_id),
    KEY idx_customer_profile_staff (preferred_staff_id),

    CONSTRAINT fk_customer_profile_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_customer_preferred_staff
        FOREIGN KEY (preferred_staff_id)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS social_accounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    provider ENUM(
        'google',
        'facebook'
    ) NOT NULL,
    provider_user_id VARCHAR(255) NOT NULL,
    provider_email VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_social_provider_user (
        provider,
        provider_user_id
    ),
    KEY idx_social_user (user_id),

    CONSTRAINT fk_social_account_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 6. SERVICES
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS services (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    duration INT UNSIGNED NOT NULL DEFAULT 60,
    status ENUM(
        'active',
        'inactive'
    ) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_services_status (status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 7. BOOKINGS
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED DEFAULT NULL,
    service_id INT UNSIGNED DEFAULT NULL,
    staff_id INT UNSIGNED DEFAULT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    status ENUM(
        'pending',
        'confirmed',
        'completed',
        'cancelled',
        'no_show'
    ) NOT NULL DEFAULT 'pending',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_bookings_customer (customer_id),
    KEY idx_bookings_service (service_id),
    KEY idx_bookings_staff_date (staff_id, booking_date),
    KEY idx_bookings_status_date (status, booking_date),

    CONSTRAINT fk_booking_customer
        FOREIGN KEY (customer_id)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_booking_service
        FOREIGN KEY (service_id)
        REFERENCES services(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_booking_staff
        FOREIGN KEY (staff_id)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 8. PAYMENTS
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED DEFAULT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_method VARCHAR(50) DEFAULT NULL,
    reference_number VARCHAR(100) DEFAULT NULL,
    proof_path VARCHAR(255) DEFAULT NULL,
    status ENUM(
        'pending',
        'verified',
        'rejected'
    ) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_payments_booking (booking_id),
    KEY idx_payments_status_created (status, created_at),

    CONSTRAINT fk_payment_booking
        FOREIGN KEY (booking_id)
        REFERENCES bookings(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 9. INVENTORY
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS inventory (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(150) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    reorder_level DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    unit VARCHAR(50) DEFAULT NULL,
    status ENUM(
        'in_stock',
        'low_stock',
        'out_of_stock'
    ) NOT NULL DEFAULT 'in_stock',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_inventory_status (status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 10. PROMOTIONS
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS promotions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    discount_value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    status ENUM(
        'active',
        'inactive',
        'expired'
    ) NOT NULL DEFAULT 'inactive',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_promotions_status_dates (
        status,
        start_date,
        end_date
    )
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 11. CUSTOMER FEEDBACK
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS feedback (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED DEFAULT NULL,
    booking_id INT UNSIGNED DEFAULT NULL,
    rating TINYINT UNSIGNED DEFAULT NULL,
    comment TEXT DEFAULT NULL,
    status ENUM(
        'new',
        'reviewed',
        'flagged'
    ) NOT NULL DEFAULT 'new',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_feedback_customer (customer_id),
    KEY idx_feedback_booking (booking_id),
    KEY idx_feedback_status (status),

    CONSTRAINT fk_feedback_customer
        FOREIGN KEY (customer_id)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_feedback_booking
        FOREIGN KEY (booking_id)
        REFERENCES bookings(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT chk_feedback_rating
        CHECK (
            rating IS NULL
            OR rating BETWEEN 1 AND 5
        )
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 12. AUDIT LOGS
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    username VARCHAR(100) NOT NULL,
    action VARCHAR(150) NOT NULL,
    module VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_audit_user_id (user_id),
    KEY idx_audit_module (module),
    KEY idx_audit_created_at (created_at),

    CONSTRAINT fk_audit_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 13. DEFAULT SETTINGS
-- ------------------------------------------------------------

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
ON DUPLICATE KEY UPDATE
    salon_name = VALUES(salon_name),
    opening_time = VALUES(opening_time),
    closing_time = VALUES(closing_time),
    downpayment_percentage = VALUES(downpayment_percentage),
    grace_period_minutes = VALUES(grace_period_minutes),
    timezone = VALUES(timezone);

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
ON DUPLICATE KEY UPDATE
    nail_generator_enabled = VALUES(nail_generator_enabled),
    chatbot_enabled = VALUES(chatbot_enabled),
    receipt_verification_enabled = VALUES(receipt_verification_enabled),
    style_recommendation_enabled = VALUES(style_recommendation_enabled),
    daily_generation_limit = VALUES(daily_generation_limit),
    ai_service_url = VALUES(ai_service_url);

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
ON DUPLICATE KEY UPDATE
    sms_enabled = VALUES(sms_enabled),
    email_enabled = VALUES(email_enabled),
    push_enabled = VALUES(push_enabled),
    booking_confirmation_enabled = VALUES(booking_confirmation_enabled),
    payment_confirmation_enabled = VALUES(payment_confirmation_enabled),
    promotion_enabled = VALUES(promotion_enabled),
    reminder_minutes = VALUES(reminder_minutes),
    sms_sender_name = VALUES(sms_sender_name);

INSERT INTO localization_settings (
    id,
    language_code
)
VALUES (
    1,
    'english'
)
ON DUPLICATE KEY UPDATE
    language_code = language_code;

-- ------------------------------------------------------------
-- 14. DEFAULT SUPER ADMIN ACCOUNT
-- ------------------------------------------------------------
--
-- Username: SupAdmin
-- Password: SupAdmin
--
-- The password below is securely hashed with PHP password_hash().
-- ------------------------------------------------------------

INSERT INTO users (
    fullname,
    username,
    email,
    phone_number,
    password,
    role,
    status
)
VALUES (
    'System Super Administrator',
    'SupAdmin',
    'superadmin@nbw.local',
    NULL,
    '$2y$12$AF9o69L1OJFcKA0RfKLDjeguu825xcOpPEp2fudFdzmkh3sRcd7R6',
    'super_admin',
    'active'
)
ON DUPLICATE KEY UPDATE
    fullname = VALUES(fullname),
    email = VALUES(email),
    password = VALUES(password),
    role = VALUES(role),
    status = VALUES(status);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- END OF SMARTBOOKING V2 CONSOLIDATED DATABASE
-- ============================================================
