-- SmartBooking V2: single Nails & Body Worx salon.
-- Select the target database in phpMyAdmin/MySQL before running this file.
-- BACK UP the whole database and uploads; stop all web/worker writes first.
-- No business row, legacy branch row, column, index or foreign key is dropped.
-- Default target: BR-001, otherwise lowest existing ID. To override, run
-- SET @single_salon_branch_id = <existing ID>; in this SAME SQL session first.
-- All failures roll back business changes. Empty support tables may remain.

CREATE TABLE IF NOT EXISTS single_salon_config (
    id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    branch_id INT UNSIGNED NOT NULL,
    migrated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS single_salon_assignment_backup (
    table_name VARCHAR(64) NOT NULL,
    record_id BIGINT UNSIGNED NOT NULL,
    original_branch_id BIGINT NULL,
    original_branch_label VARCHAR(150) NULL,
    PRIMARY KEY (table_name, record_id)
) ENGINE=InnoDB;

-- LIKE preserves column types and does not copy foreign keys.
CREATE TABLE IF NOT EXISTS single_salon_branches_backup LIKE branches;

DELIMITER $$
DROP PROCEDURE IF EXISTS sb_migrate_single_salon $$
CREATE PROCEDURE sb_migrate_single_salon()
main: BEGIN
    DECLARE target_id INT UNSIGNED;
    DECLARE n INT DEFAULT 0;
    DECLARE done INT DEFAULT 0;
    DECLARE target_table VARCHAR(64);
    DECLARE has_updated_at INT DEFAULT 0;
    DECLARE tables_to_move CURSOR FOR
        SELECT table_name FROM information_schema.columns
        WHERE table_schema = DATABASE() AND column_name = 'branch_id'
          AND table_name IN ('users','bookings','inventory','promotions',
              'staff_weekly_availability','staff_schedule_exceptions',
              'suppliers','inventory_movements');
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    -- This migration supports the inspected V2 schema. Refuse unknown variants.
    SELECT COUNT(*) INTO n FROM information_schema.columns
    WHERE table_schema=DATABASE() AND column_name='branch_id'
      AND table_name IN ('users','bookings','inventory','promotions',
          'staff_weekly_availability','staff_schedule_exceptions',
          'suppliers','inventory_movements');
    IF n <> 8 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Missing V2 branch columns. Apply the existing V2 schema updates before 201.';
    END IF;
    SELECT COUNT(*) INTO n FROM information_schema.columns
    WHERE table_schema=DATABASE() AND column_name LIKE '%branch%'
      AND NOT (column_name='branch_id' AND table_name IN
          ('users','bookings','inventory','promotions','staff_weekly_availability',
           'staff_schedule_exceptions','suppliers','inventory_movements','single_salon_config'))
      AND NOT (column_name='branch' AND table_name='staff_profiles')
      AND table_name NOT IN ('branches','single_salon_branches_backup','single_salon_assignment_backup');
    IF n > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Unrecognized branch dependency. Review this schema before consolidating.';
    END IF;
    SELECT COUNT(*) INTO n FROM information_schema.key_column_usage
    WHERE table_schema=DATABASE() AND referenced_table_name='branches'
      AND NOT (column_name='branch_id' AND referenced_column_name='id'
        AND table_name IN ('users','bookings','inventory','promotions',
          'staff_weekly_availability','staff_schedule_exceptions','suppliers','inventory_movements','single_salon_config'));
    IF n > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Unrecognized foreign key to branches. Review dependencies first.';
    END IF;
    SELECT COUNT(*) INTO n FROM information_schema.tables
    WHERE table_schema=DATABASE() AND table_name IN
       ('branches','users','bookings','inventory','promotions','staff_profiles',
        'staff_weekly_availability','staff_schedule_exceptions','suppliers','inventory_movements',
        'single_salon_config','single_salon_assignment_backup','single_salon_branches_backup')
      AND engine <> 'InnoDB';
    IF n > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='All affected tables must use InnoDB for transactional recovery.';
    END IF;

    IF EXISTS (SELECT 1 FROM single_salon_config WHERE id=1) THEN
        SELECT 'Already migrated; original backups and assignments retained.' AS result;
        LEAVE main;
    END IF;
    IF EXISTS (SELECT 1 FROM single_salon_assignment_backup)
       OR EXISTS (SELECT 1 FROM single_salon_branches_backup) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Backup rows exist without a completion marker. Review recovery state; do not overwrite backups.';
    END IF;

    SET target_id = COALESCE(@single_salon_branch_id,
        (SELECT id FROM branches WHERE branch_code='BR-001' LIMIT 1),
        (SELECT MIN(id) FROM branches));
    IF target_id IS NULL OR NOT EXISTS (SELECT 1 FROM branches WHERE id=target_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='No valid salon record. Set @single_salon_branch_id to an existing ID.';
    END IF;
    -- Two schedules for the same person/day cannot safely be chosen automatically.
    SELECT COUNT(*) INTO n FROM (
        SELECT staff_id, day_of_week FROM staff_weekly_availability
        GROUP BY staff_id, day_of_week HAVING COUNT(*) > 1
    ) conflicts;
    IF n > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Conflicting weekly schedules for a staff/day. Review and resolve before migration; no business data changed.';
    END IF;
    SELECT COUNT(*) INTO n FROM (
        SELECT staff_id, exception_date FROM staff_schedule_exceptions
        WHERE exception_type='custom_hours'
        GROUP BY staff_id, exception_date HAVING COUNT(*) > 1
    ) conflicts;
    IF n > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Multiple custom-hours rules for a staff/date. Resolve before migration; no business data changed.';
    END IF;

    START TRANSACTION;
    INSERT INTO single_salon_branches_backup SELECT * FROM branches;
    OPEN tables_to_move;
    move_loop: LOOP
        FETCH tables_to_move INTO target_table;
        IF done THEN LEAVE move_loop; END IF;
        SET @sb_sql = CONCAT('INSERT INTO single_salon_assignment_backup (table_name,record_id,original_branch_id) SELECT ',
            QUOTE(target_table), ',id,branch_id FROM `', target_table, '`');
        PREPARE sb_stmt FROM @sb_sql;
        EXECUTE sb_stmt;
        DEALLOCATE PREPARE sb_stmt;
        SELECT COUNT(*) INTO has_updated_at FROM information_schema.columns
        WHERE table_schema=DATABASE() AND table_name=target_table AND column_name='updated_at';
        SET @sb_sql = CONCAT('UPDATE `',target_table,'` SET branch_id=',target_id,
            IF(has_updated_at,', updated_at=updated_at',''),
            ' WHERE NOT (branch_id <=> ',target_id,')');
        PREPARE sb_stmt FROM @sb_sql;
        EXECUTE sb_stmt;
        DEALLOCATE PREPARE sb_stmt;
    END LOOP;
    CLOSE tables_to_move;
    INSERT INTO single_salon_assignment_backup (table_name,record_id,original_branch_label)
        SELECT 'staff_profiles',user_id,branch FROM staff_profiles;
    UPDATE staff_profiles SET branch='Nails & Body Worx', updated_at=updated_at;
    UPDATE branches SET branch_name='Nails & Body Worx', updated_at=updated_at WHERE id=target_id;
    INSERT INTO single_salon_config (id,branch_id) VALUES (1,target_id);
    COMMIT;
    SELECT 'Single salon migration complete; all business rows retained.' AS result, target_id AS salon_id;
END $$
CALL sb_migrate_single_salon() $$
DROP PROCEDURE IF EXISTS sb_migrate_single_salon $$
DELIMITER ;
