USE smartbooking_v2;

CREATE TABLE IF NOT EXISTS staff_weekly_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    branch_id INT NOT NULL,
    day_of_week TINYINT NOT NULL,
    is_working TINYINT(1) NOT NULL DEFAULT 1,
    start_time TIME NULL,
    end_time TIME NULL,
    break_start TIME NULL,
    break_end TIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_staff_weekly_day (staff_id, branch_id, day_of_week),
    KEY idx_staff_weekly_branch (branch_id, staff_id),
    CHECK (day_of_week BETWEEN 1 AND 7)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS staff_schedule_exceptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    branch_id INT NOT NULL,
    exception_date DATE NOT NULL,
    exception_type ENUM('day_off','unavailable','custom_hours')
        NOT NULL DEFAULT 'unavailable',
    start_time TIME NULL,
    end_time TIME NULL,
    reason VARCHAR(255) NULL,
    created_by INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_staff_exception_lookup
        (staff_id, branch_id, exception_date, exception_type),
    KEY idx_staff_exception_branch (branch_id, exception_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO staff_weekly_availability
    (staff_id, branch_id, day_of_week, is_working,
     start_time, end_time, break_start, break_end)
SELECT u.id, u.branch_id, days.day_number,
       CASE WHEN days.day_number = 7 THEN 0 ELSE 1 END,
       '09:00:00', '18:00:00', '12:00:00', '13:00:00'
FROM users u
CROSS JOIN (
    SELECT 1 AS day_number UNION ALL SELECT 2 UNION ALL SELECT 3
    UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6
    UNION ALL SELECT 7
) days
WHERE u.role = 'staff'
  AND u.branch_id IS NOT NULL
ON DUPLICATE KEY UPDATE staff_id = VALUES(staff_id);
