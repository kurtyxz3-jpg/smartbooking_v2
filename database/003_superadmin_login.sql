USE smartbooking_v2;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(150) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) DEFAULT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'staff', 'customer') NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO users (
    fullname,
    username,
    email,
    password,
    role,
    status
)
VALUES (
    'System Super Administrator',
    'SupAdmin',
    'superadmin@nbw.local',
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
