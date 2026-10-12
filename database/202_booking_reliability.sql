DELIMITER $$
DROP PROCEDURE IF EXISTS nbw_check_202$$
CREATE PROCEDURE nbw_check_202()
BEGIN
 IF NOT EXISTS(SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='single_salon_config') THEN
 SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Apply 201_single_salon.sql first';
 END IF;
 IF (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name IN ('bookings','users','services','payments','staff_weekly_availability','staff_schedule_exceptions') AND engine='InnoDB') <> 6 THEN
 SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Required tables must exist and use InnoDB';
 END IF;
 IF EXISTS(SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE()
    AND table_name IN ('password_reset_tokens','account_security_versions','booking_write_mutex','appointment_events','notification_deliveries','recovery_attempts') AND engine<>'InnoDB') THEN
 SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Recovery and notification tables must use InnoDB';
 END IF;
END$$
CALL nbw_check_202()$$
DROP PROCEDURE nbw_check_202$$
DELIMITER ;
-- Apply AFTER 201_single_salon.sql to a backed-up database in maintenance mode.

CREATE TABLE IF NOT EXISTS password_reset_tokens(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 token_hash CHAR(64) NOT NULL UNIQUE,
 expires_at DATETIME NOT NULL,
 used_at DATETIME DEFAULT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_password_reset_user(user_id),
 INDEX idx_password_reset_expiry(expires_at),
 CONSTRAINT fk_password_reset_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS account_notification_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notification_key VARCHAR(100) NOT NULL,
    enabled TINYINT(1) NOT NULL DEFAULT 1,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_account_notification_user_key (
        user_id,
        notification_key
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE bookings ADD COLUMN IF NOT EXISTS estimated_duration INT UNSIGNED DEFAULT NULL;
-- Additive only: no existing business rows are deleted or reassigned.
CREATE TABLE IF NOT EXISTS booking_write_mutex (id TINYINT PRIMARY KEY) ENGINE=InnoDB;
INSERT IGNORE INTO booking_write_mutex VALUES (1);
CREATE TABLE IF NOT EXISTS recovery_attempts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 ip_hash CHAR(64) NOT NULL, identity_hash CHAR(64) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX recovery_ip (ip_hash,created_at), INDEX recovery_identity (identity_hash,created_at)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS appointment_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 event_key VARCHAR(190) NULL UNIQUE,
 event_type VARCHAR(30) NOT NULL,
 booking_id INT UNSIGNED NULL, user_id INT UNSIGNED NULL,
 booking_date DATE NULL, booking_time TIME NULL, booking_status VARCHAR(30) NULL,
 staff_id INT UNSIGNED NULL, previous_staff_id INT UNSIGNED NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 expanded_at DATETIME NULL,
 INDEX event_pending (expanded_at,id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS notification_deliveries (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 event_id BIGINT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL,
 channel VARCHAR(10) NOT NULL, preference_key VARCHAR(40) NOT NULL,
 state VARCHAR(20) NOT NULL DEFAULT 'pending', attempts INT NOT NULL DEFAULT 0,
 available_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 sent_at DATETIME NULL, last_error VARCHAR(190) NULL,
 UNIQUE KEY delivery_once (event_id,user_id,channel),
 INDEX delivery_pending (state,available_at)
) ENGINE=InnoDB;
-- These triggers enqueue metadata in the SAME transaction as booking changes.
-- They do not send messages or change existing appointment records.
DROP TRIGGER IF EXISTS nbw_appointment_created;
DROP TRIGGER IF EXISTS nbw_appointment_updated;
DELIMITER $$
CREATE TRIGGER nbw_appointment_created AFTER INSERT ON bookings FOR EACH ROW
BEGIN
 INSERT INTO appointment_events(event_type,booking_id,booking_date,booking_time,booking_status,staff_id)
 VALUES('created',NEW.id,NEW.booking_date,NEW.booking_time,NEW.status,NEW.staff_id);
END$$
CREATE TRIGGER nbw_appointment_updated AFTER UPDATE ON bookings FOR EACH ROW
BEGIN
 IF NOT(OLD.status <=> NEW.status) OR NOT(OLD.booking_date <=> NEW.booking_date)
    OR NOT(OLD.booking_time <=> NEW.booking_time) OR NOT(OLD.staff_id <=> NEW.staff_id) THEN
  INSERT INTO appointment_events(event_type,booking_id,booking_date,booking_time,booking_status,staff_id,previous_staff_id)
  VALUES(IF(NOT(OLD.booking_date <=> NEW.booking_date) OR NOT(OLD.booking_time <=> NEW.booking_time),'rescheduled','updated'),
   NEW.id,NEW.booking_date,NEW.booking_time,NEW.status,NEW.staff_id,OLD.staff_id);
 END IF;
END$$
DELIMITER ;

CREATE TABLE IF NOT EXISTS account_security_versions (
 user_id INT UNSIGNED PRIMARY KEY, version INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB;
