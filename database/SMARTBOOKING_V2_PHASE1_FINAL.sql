
-- ===== SOURCE: smartbooking_v2_latest.sql =====
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


-- ===== SOURCE: database/031_real_services_catalog.sql =====
USE smartbooking_v2;
CREATE TABLE IF NOT EXISTS service_categories(
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 slug VARCHAR(100) NOT NULL UNIQUE,
 name VARCHAR(150) NOT NULL,
 description TEXT DEFAULT NULL,
 image VARCHAR(255) DEFAULT NULL,
 display_order INT UNSIGNED NOT NULL DEFAULT 0,
 status ENUM('active','inactive') NOT NULL DEFAULT 'active',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE services
 ADD COLUMN IF NOT EXISTS category_id INT UNSIGNED DEFAULT NULL AFTER id,
 ADD COLUMN IF NOT EXISTS slug VARCHAR(180) DEFAULT NULL AFTER category_id,
 ADD COLUMN IF NOT EXISTS price_note VARCHAR(100) DEFAULT NULL AFTER price,
 ADD COLUMN IF NOT EXISTS image VARCHAR(255) DEFAULT NULL AFTER duration,
 ADD COLUMN IF NOT EXISTS featured TINYINT(1) NOT NULL DEFAULT 0 AFTER image,
 ADD COLUMN IF NOT EXISTS display_order INT UNSIGNED NOT NULL DEFAULT 0 AFTER featured;

INSERT INTO service_categories(id,slug,name,description,image,display_order,status) VALUES
(1,'nail-care','Nail Care','Manicure, pedicure, gel polish, extensions, removal, and nail art.','assets/images/services/nails/nails-01.webp',1,'active'),
(2,'spa-massage','Spa & Massage','Foot spa, massage, reflexology, scrubs, and relaxing body care.','assets/images/services/salon/salon-02.webp',2,'active'),
(3,'lashes-brows','Lashes & Brows','Lash lifting, tint, threading, extensions, and brow lamination.','assets/images/services/salon/salon-16.webp',3,'active'),
(4,'hair','Hair Services','Haircut, color, highlights, rebond, Brazilian, and Botox treatments.','assets/images/services/campaigns/campaigns-04.webp',4,'active'),
(5,'facial-skin','Facial & Skin','Facials, peels, carbon laser, hydration, and skin treatments.','assets/images/services/campaigns/campaigns-07.webp',5,'active'),
(6,'hair-removal','Waxing & Hair Removal','Waxing and diode-laser treatments for different body areas.','assets/images/services/campaigns/campaigns-15.webp',6,'active'),
(7,'advanced-aesthetic','Advanced Aesthetic','Contouring, lifting, fillers, Hiko nose lift, and advanced treatments.','assets/images/services/campaigns/campaigns-11.webp',7,'active'),
(8,'semi-permanent','Semi-Permanent Makeup','Brows, eyeliner, lip micropigmentation, and reconstruction.','assets/images/services/lips/lips-01.webp',8,'active'),
(9,'kiddie','Kiddie Services','Gentle nail and spa care for younger guests.','assets/images/services/campaigns/campaigns-23.webp',9,'active')
ON DUPLICATE KEY UPDATE name=VALUES(name),description=VALUES(description),image=VALUES(image),display_order=VALUES(display_order),status=VALUES(status);

INSERT INTO services(id,category_id,slug,name,description,price,price_note,duration,image,featured,display_order,status) VALUES
(1,1,'manicure','Manicure','Classic nail shaping, cuticle care, and regular polish.',120.00,'',30,'assets/images/services/nails/nails-04.webp',1,1,'active'),
(2,1,'pedicure','Pedicure','Classic foot and nail care with regular polish.',130.00,'',40,'assets/images/services/salon/salon-01.webp',1,2,'active'),
(3,1,'gel-polish-hands','Gel Polish - Hands','Long-wearing gel color for the hands.',350.00,'',60,'assets/images/services/nails/nails-14.webp',1,3,'active'),
(4,1,'gel-polish-feet','Gel Polish - Feet','Long-wearing gel color for the feet.',400.00,'',60,'assets/images/services/salon/salon-24.webp',0,4,'active'),
(5,1,'softgel-extension-with-gel-polish','Softgel Extension with Gel Polish','Softgel extensions finished with your selected gel color.',800.00,'',120,'assets/images/services/nails/nails-10.webp',1,5,'active'),
(6,1,'softgel-per-nail','Softgel per Nail','Single-nail softgel extension or repair.',100.00,'per nail',20,'assets/images/services/nails/nails-11.webp',0,6,'active'),
(7,1,'softgel-removal','Softgel Removal','Careful removal of a full softgel set.',200.00,'',45,'assets/images/services/nails/nails-08.webp',0,7,'active'),
(8,1,'polygel-acrylic-removal','Polygel/Acrylic Removal','Removal of polygel or acrylic extensions.',250.00,'',50,'assets/images/services/nails/nails-09.webp',0,8,'active'),
(9,1,'polygel-infill-with-gel-polish','Polygel Infill with Gel Polish','Polygel maintenance with fresh gel polish.',600.00,'',100,'assets/images/services/nails/nails-13.webp',0,9,'active'),
(10,1,'nail-art','Nail Art','Custom nail-art details based on complexity.',150.00,'₱150-₱200',30,'assets/images/services/nails/nails-01.webp',1,10,'active'),
(11,1,'stones-and-embellishments','Stones and Embellishments','Decorative stones or embellishments.',20.00,'₱20-₱50 each',10,'assets/images/services/nails/nails-02.webp',0,11,'active'),
(12,1,'french-tip-regular-polish','French Tip - Regular Polish','Classic French-tip add-on using regular polish.',50.00,'add-on',20,'assets/images/services/nails/nails-05.webp',0,12,'active'),
(13,1,'change-gel-polish','Change Gel Polish','Refresh your current set with a new gel color.',250.00,'',45,'assets/images/services/nails/nails-16.webp',0,13,'active'),
(14,1,'gel-polish-removal','Gel Polish Removal','Gentle gel-polish removal.',50.00,'',20,'assets/images/services/nails/nails-17.webp',0,14,'active'),
(15,2,'foot-spa-ladies','Foot Spa - Ladies','Soak, exfoliation, callus care, and relaxing foot treatment.',300.00,'',50,'assets/images/services/salon/salon-05.webp',1,15,'active'),
(16,2,'foot-spa-gentlemen','Foot Spa - Gentlemen','Foot spa care tailored for gentlemen.',350.00,'',50,'assets/images/services/salon/salon-08.webp',0,16,'active'),
(17,2,'hand-scrub','Hand Scrub','Exfoliating hand treatment for smoother skin.',250.00,'',30,'assets/images/services/salon/salon-10.webp',0,17,'active'),
(18,2,'whitening-mask','Whitening Mask','Brightening mask add-on for hands or feet.',100.00,'',25,'assets/images/services/salon/salon-12.webp',0,18,'active'),
(19,2,'whitening-full-body-scrub','Whitening Full Body Scrub','Full-body exfoliating and brightening treatment.',800.00,'',75,'assets/images/services/campaigns/campaigns-05.webp',1,19,'active'),
(20,2,'regular-massage-1-hour','Regular Massage - 1 Hour','Relaxing full-body massage.',500.00,'',60,'assets/images/services/campaigns/campaigns-19.webp',1,20,'active'),
(21,2,'massage-with-stretching-1-hour','Massage with Stretching - 1 Hour','Massage paired with guided stretching.',600.00,'',60,'assets/images/services/salon/salon-03.webp',0,21,'active'),
(22,2,'ventosa-massage-1-hour','Ventosa Massage - 1 Hour','Massage with traditional ventosa treatment.',650.00,'',60,'assets/images/services/salon/salon-06.webp',0,22,'active'),
(23,2,'massage-with-ear-candle-1-hour','Massage with Ear Candle - 1 Hour','Full massage combined with ear candling.',680.00,'',60,'assets/images/services/salon/salon-07.webp',0,23,'active'),
(24,2,'hot-stone-massage-1-5-hours','Hot Stone Massage - 1.5 Hours','Heated-stone massage for deep relaxation.',800.00,'',90,'assets/images/services/campaigns/campaigns-30.webp',1,24,'active'),
(25,2,'head-spa-1-hour','Head Spa - 1 Hour','Scalp cleansing, massage, and hair-care ritual.',1200.00,'',60,'assets/images/services/campaigns/campaigns-24.webp',1,25,'active'),
(26,2,'back-massage-30-minutes','Back Massage - 30 Minutes','Focused back massage.',350.00,'',30,'assets/images/services/salon/salon-22.webp',0,26,'active'),
(27,2,'hand-massage-30-minutes','Hand Massage - 30 Minutes','Focused hand and arm massage.',250.00,'',30,'assets/images/services/salon/salon-23.webp',0,27,'active'),
(28,2,'foot-massage-30-minutes','Foot Massage - 30 Minutes','Focused foot and lower-leg massage.',300.00,'',30,'assets/images/services/salon/salon-04.webp',0,28,'active'),
(29,3,'threading','Threading','Precision facial threading.',100.00,'',25,'assets/images/services/salon/salon-16.webp',0,29,'active'),
(30,3,'eyelash-lift','Eyelash Lift','Natural lash-lifting treatment.',500.00,'',60,'assets/images/services/campaigns/campaigns-25.webp',1,30,'active'),
(31,3,'eyelash-lift-with-tint','Eyelash Lift with Tint','Lash lift enhanced with tint.',600.00,'',75,'assets/images/services/campaigns/campaigns-27.webp',1,31,'active'),
(32,3,'eyelash-extension-removal','Eyelash Extension Removal','Gentle eyelash-extension removal.',100.00,'₱100-₱150',30,'assets/images/services/salon/salon-17.webp',0,32,'active'),
(33,3,'brow-lamination','Brow Lamination','Brow shaping and lamination.',500.00,'',60,'assets/images/services/campaigns/campaigns-09.webp',1,33,'active'),
(34,9,'kiddie-manicure','Kiddie Manicure','Gentle manicure for children.',50.00,'',25,'assets/images/services/campaigns/campaigns-23.webp',0,34,'active'),
(35,9,'kiddie-pedicure','Kiddie Pedicure','Gentle pedicure for children.',50.00,'',25,'assets/images/services/campaigns/campaigns-23.webp',0,35,'active'),
(36,9,'kiddie-polish-only','Kiddie Polish Only','Quick polish application for children.',30.00,'',15,'assets/images/services/campaigns/campaigns-23.webp',0,36,'active'),
(37,9,'kiddie-foot-spa','Kiddie Foot Spa','Gentle foot spa for children.',120.00,'',35,'assets/images/services/campaigns/campaigns-23.webp',0,37,'active'),
(38,9,'kiddie-package','Kiddie Package','Manicure, pedicure, foot spa, and mask.',250.00,'',70,'assets/images/services/campaigns/campaigns-23.webp',1,38,'active'),
(39,4,'hair-cut','Hair Cut','Professional salon hair service based on the supplied pricelist.',150.00,'',35,'assets/images/services/campaigns/campaigns-04.webp',1,39,'active'),
(40,4,'blower','Blower','Professional salon hair service based on the supplied pricelist.',100.00,'',30,'assets/images/services/campaigns/campaigns-04.webp',0,40,'active'),
(41,4,'shampoo','Shampoo','Professional salon hair service based on the supplied pricelist.',100.00,'',20,'assets/images/services/campaigns/campaigns-04.webp',0,41,'active'),
(42,4,'hair-iron','Hair Iron','Professional salon hair service based on the supplied pricelist.',200.00,'',40,'assets/images/services/campaigns/campaigns-04.webp',0,42,'active'),
(43,4,'hair-all-in-package','Hair All-in Package','Professional salon hair service based on the supplied pricelist.',350.00,'',70,'assets/images/services/campaigns/campaigns-04.webp',1,43,'active'),
(44,4,'hair-color-short','Hair Color - Short','Professional salon hair service based on the supplied pricelist.',600.00,'',100,'assets/images/services/campaigns/campaigns-04.webp',0,44,'active'),
(45,4,'hair-color-medium','Hair Color - Medium','Professional salon hair service based on the supplied pricelist.',900.00,'',120,'assets/images/services/campaigns/campaigns-04.webp',0,45,'active'),
(46,4,'hair-color-long','Hair Color - Long','Professional salon hair service based on the supplied pricelist.',1100.00,'',150,'assets/images/services/campaigns/campaigns-04.webp',0,46,'active'),
(47,4,'highlights-short','Highlights - Short','Professional salon hair service based on the supplied pricelist.',600.00,'',100,'assets/images/services/campaigns/campaigns-04.webp',0,47,'active'),
(48,4,'brazilian-treatment-short','Brazilian Treatment - Short','Professional salon hair service based on the supplied pricelist.',600.00,'',120,'assets/images/services/campaigns/campaigns-04.webp',0,48,'active'),
(49,4,'hair-botox-short','Hair Botox - Short','Professional salon hair service based on the supplied pricelist.',600.00,'',120,'assets/images/services/campaigns/campaigns-04.webp',0,49,'active'),
(50,4,'rebond-short','Rebond - Short','Professional salon hair service based on the supplied pricelist.',1200.00,'',180,'assets/images/services/campaigns/campaigns-04.webp',1,50,'active'),
(51,4,'rebond-color-short','Rebond + Color - Short','Professional salon hair service based on the supplied pricelist.',1700.00,'',210,'assets/images/services/campaigns/campaigns-04.webp',0,51,'active'),
(52,5,'regular-facial-with-mask','Regular Facial with Mask','Facial cleansing and mask treatment.',500.00,'',60,'assets/images/services/campaigns/campaigns-07.webp',1,52,'active'),
(53,5,'anti-aging-24k-gold-mask','Anti-aging 24K Gold Mask','Facial with a 24K gold mask.',650.00,'',60,'assets/images/services/campaigns/campaigns-31.webp',0,53,'active'),
(54,5,'diamond-peel','Diamond Peel','Exfoliating diamond-peel facial.',700.00,'',60,'assets/images/services/campaigns/campaigns-26.webp',1,54,'active'),
(55,5,'regular-facial-rf-face','Regular Facial + RF Face','Regular facial combined with RF face treatment.',800.00,'',75,'assets/images/services/campaigns/campaigns-08.webp',0,55,'active'),
(56,5,'supreme-diamond-peel','Supreme Diamond Peel','Enhanced diamond-peel treatment.',850.00,'',75,'assets/images/services/campaigns/campaigns-26.webp',0,56,'active'),
(57,5,'melasma-laser-removal','Melasma Laser Removal','Laser treatment for melasma concerns; consultation required.',1000.00,'',60,'assets/images/services/campaigns/campaigns-13.webp',0,57,'active'),
(58,5,'korean-bb-glow','Korean BB Glow','BB Glow facial treatment.',1200.00,'',90,'assets/images/services/campaigns/campaigns-07.webp',0,58,'active'),
(59,5,'blackdoll-carbon-laser','Blackdoll Carbon Laser','Carbon laser facial treatment.',1200.00,'',75,'assets/images/services/campaigns/campaigns-13.webp',1,59,'active'),
(60,5,'hydra-3-in-1-facial','Hydra 3-in-1 Facial','Multi-step hydrating facial treatment.',1500.00,'',90,'assets/images/services/campaigns/campaigns-26.webp',1,60,'active'),
(61,5,'ultimate-hydra-10-in-1-facial','Ultimate Hydra 10-in-1 Facial','Comprehensive multi-step hydrating facial.',2000.00,'',120,'assets/images/services/campaigns/campaigns-26.webp',1,61,'active'),
(62,5,'glutathione-drip','Glutathione Drip','Glutathione drip service; consultation required.',1500.00,'',60,'assets/images/services/campaigns/campaigns-03.webp',0,62,'active'),
(63,6,'underarm-waxing','Underarm Waxing','Hair-removal service based on the supplied salon pricelist.',250.00,'',30,'assets/images/services/campaigns/campaigns-15.webp',1,63,'active'),
(64,6,'full-arms-waxing','Full Arms Waxing','Hair-removal service based on the supplied salon pricelist.',400.00,'',45,'assets/images/services/campaigns/campaigns-15.webp',0,64,'active'),
(65,6,'half-legs-waxing','Half Legs Waxing','Hair-removal service based on the supplied salon pricelist.',450.00,'',45,'assets/images/services/campaigns/campaigns-15.webp',0,65,'active'),
(66,6,'full-legs-waxing','Full Legs Waxing','Hair-removal service based on the supplied salon pricelist.',650.00,'',60,'assets/images/services/campaigns/campaigns-15.webp',0,66,'active'),
(67,6,'bikini-waxing','Bikini Waxing','Hair-removal service based on the supplied salon pricelist.',700.00,'',45,'assets/images/services/campaigns/campaigns-15.webp',0,67,'active'),
(68,6,'diode-laser-underarm','Diode Laser - Underarm','Hair-removal service based on the supplied salon pricelist.',700.00,'',45,'assets/images/services/campaigns/campaigns-06.webp',1,68,'active'),
(69,6,'diode-laser-bikini','Diode Laser - Bikini','Hair-removal service based on the supplied salon pricelist.',800.00,'',45,'assets/images/services/campaigns/campaigns-06.webp',0,69,'active'),
(70,6,'diode-laser-full-arms','Diode Laser - Full Arms','Hair-removal service based on the supplied salon pricelist.',1100.00,'',60,'assets/images/services/campaigns/campaigns-14.webp',0,70,'active'),
(71,6,'diode-laser-full-legs','Diode Laser - Full Legs','Hair-removal service based on the supplied salon pricelist.',1500.00,'',75,'assets/images/services/campaigns/campaigns-14.webp',1,71,'active'),
(72,7,'12d-hifu','12D HIFU','Face contouring and lifting treatment; consultation required.',4500.00,'',90,'assets/images/services/campaigns/campaigns-11.webp',1,72,'active'),
(73,7,'emsculpt-30-minutes','EMSculpt - 30 Minutes','Non-invasive body contouring session.',1000.00,'',30,'assets/images/services/campaigns/campaigns-10.webp',1,73,'active'),
(74,7,'rf-face','RF Face','Radio-frequency face treatment.',350.00,'',30,'assets/images/services/campaigns/campaigns-08.webp',0,74,'active'),
(75,7,'mesolipo-double-chin','Mesolipo - Double Chin','Mesolipo treatment for the double-chin area; consultation required.',1500.00,'',60,'assets/images/services/campaigns/campaigns-17.webp',0,75,'active'),
(76,7,'lip-filler','Lip Filler','Hyaluronic-acid lip filler; consultation required.',8000.00,'',60,'assets/images/services/campaigns/campaigns-16.webp',1,76,'active'),
(77,7,'chin-filler','Chin Filler','Hyaluronic-acid chin filler; consultation required.',8000.00,'',60,'assets/images/services/campaigns/campaigns-16.webp',0,77,'active'),
(78,7,'jaw-filler','Jaw Filler','Jaw filler treatment; consultation required.',15000.00,'',75,'assets/images/services/campaigns/campaigns-16.webp',0,78,'active'),
(79,7,'hiko-nose-lift-basic-retouch','Hiko Nose Lift - Basic/Retouch','Basic or retouch Hiko nose-lift package; consultation required.',5500.00,'',75,'assets/images/services/campaigns/campaigns-22.webp',1,79,'active'),
(80,7,'warts-skin-tag-removal','Warts/Skin Tag Removal','Warts or skin-tag removal; consultation required.',800.00,'₱800-₱2,500',45,'assets/images/services/campaigns/campaigns-20.webp',0,80,'active'),
(81,7,'body-tattoo-removal','Body Tattoo Removal','Body tattoo-removal session; consultation required.',1000.00,'₱1,000-₱2,500',60,'assets/images/services/campaigns/campaigns-20.webp',0,81,'active'),
(82,8,'ombre-brows-first-session','Ombre Brows - First Session','Semi-permanent ombre-brow procedure.',5500.00,'',150,'assets/images/services/pricelists/pricelist-01.webp',1,82,'active'),
(83,8,'nano-combo-brows-first-session','Nano Combo Brows - First Session','Nano combo-brow procedure.',4500.00,'',150,'assets/images/services/pricelists/pricelist-01.webp',0,83,'active'),
(84,8,'nano-machine-strokes-first-session','Nano Machine Strokes - First Session','Nano machine-stroke brow procedure.',7900.00,'',180,'assets/images/services/pricelists/pricelist-01.webp',0,84,'active'),
(85,8,'menstrokes-first-session','Menstrokes - First Session','Semi-permanent brow procedure designed for men.',7900.00,'',180,'assets/images/services/pricelists/pricelist-01.webp',0,85,'active'),
(86,8,'lip-micropigmentation-first-session','Lip Micropigmentation - First Session','Semi-permanent lip-color procedure.',4500.00,'',180,'assets/images/services/lips/lips-01.webp',1,86,'active'),
(87,8,'classic-winged-eyeliner-tattoo-first-session','Classic/Winged Eyeliner Tattoo - First Session','Semi-permanent eyeliner procedure.',3500.00,'₱3,500-₱4,000',150,'assets/images/services/pricelists/pricelist-01.webp',0,87,'active'),
(88,8,'brow-reconstruction','Brow Reconstruction','Cover-up and correction consultation starts at the listed charge.',500.00,'charges vary',45,'assets/images/services/pricelists/pricelist-04.webp',0,88,'active')
ON DUPLICATE KEY UPDATE category_id=VALUES(category_id),slug=VALUES(slug),name=VALUES(name),description=VALUES(description),price=VALUES(price),price_note=VALUES(price_note),duration=VALUES(duration),image=VALUES(image),featured=VALUES(featured),display_order=VALUES(display_order),status=VALUES(status);


-- ===== SOURCE: database/032_booking_wizard.sql =====
USE smartbooking_v2;

ALTER TABLE bookings
    ADD COLUMN IF NOT EXISTS booking_code VARCHAR(30) DEFAULT NULL AFTER id,
    ADD COLUMN IF NOT EXISTS quoted_price DECIMAL(10,2) DEFAULT NULL AFTER service_id,
    ADD COLUMN IF NOT EXISTS estimated_duration INT UNSIGNED DEFAULT NULL AFTER quoted_price,
    ADD COLUMN IF NOT EXISTS design_reference VARCHAR(255) DEFAULT NULL AFTER notes;

CREATE UNIQUE INDEX IF NOT EXISTS uq_bookings_booking_code
    ON bookings (booking_code);

CREATE INDEX IF NOT EXISTS idx_bookings_live_availability
    ON bookings (booking_date, booking_time, staff_id, status);


-- ===== SOURCE: database/033_service_image_correction.sql =====
USE smartbooking_v2;

-- Replace text-heavy pricelist screenshots with real procedure photos.

UPDATE services
SET image = 'assets/images/services/semi-permanent/ombre-brows.webp'
WHERE name = 'Ombre Brows - First Session';

UPDATE services
SET image = 'assets/images/services/semi-permanent/nano-combo-brows.webp'
WHERE name = 'Nano Combo Brows - First Session';

UPDATE services
SET image = 'assets/images/services/semi-permanent/nano-machine-strokes.webp'
WHERE name = 'Nano Machine Strokes - First Session';

UPDATE services
SET image = 'assets/images/services/semi-permanent/menstrokes.webp'
WHERE name = 'Menstrokes - First Session';

UPDATE services
SET image = 'assets/images/services/semi-permanent/lip-micropigmentation.webp'
WHERE name = 'Lip Micropigmentation - First Session';

UPDATE services
SET image = 'assets/images/services/semi-permanent/eyeliner-tattoo.webp'
WHERE name = 'Classic/Winged Eyeliner Tattoo - First Session';

UPDATE services
SET image = 'assets/images/services/semi-permanent/brow-reconstruction.webp'
WHERE name = 'Brow Reconstruction';

-- Verification query
SELECT name, image
FROM services
WHERE name IN (
    'Ombre Brows - First Session',
    'Nano Combo Brows - First Session',
    'Nano Machine Strokes - First Session',
    'Menstrokes - First Session',
    'Lip Micropigmentation - First Session',
    'Classic/Winged Eyeliner Tattoo - First Session',
    'Brow Reconstruction'
);

-- ===== SOURCE: database/035_phase1_completion.sql =====
USE smartbooking_v2;
CREATE TABLE IF NOT EXISTS email_verification_tokens(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 token_hash CHAR(64) NOT NULL UNIQUE,
 expires_at DATETIME NOT NULL,
 used_at DATETIME DEFAULT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_email_verification_user(user_id),
 CONSTRAINT fk_email_verification_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
