USE smartbooking_v2;

CREATE TABLE IF NOT EXISTS employment_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    employee_number VARCHAR(50) NOT NULL UNIQUE,
    date_hired DATE NOT NULL,
    employment_status ENUM('full_time', 'part_time', 'contractual')
        NOT NULL DEFAULT 'full_time',
    salary_type ENUM('monthly', 'daily', 'hourly', 'commission')
        NOT NULL DEFAULT 'monthly',
    salary_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    contract_end_date DATE DEFAULT NULL,
    emergency_contact_name VARCHAR(150) DEFAULT NULL,
    emergency_contact_number VARCHAR(50) DEFAULT NULL,
    emergency_contact_relationship VARCHAR(100) DEFAULT NULL,
    government_id_type VARCHAR(100) DEFAULT NULL,
    government_id_number VARCHAR(100) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    record_status ENUM('active', 'archived') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_employment_user (user_id),
    INDEX idx_employment_status (employment_status),
    INDEX idx_employment_record_status (record_status)
);
