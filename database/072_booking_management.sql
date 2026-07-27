USE smartbooking_v2;

-- Add the In Progress status without changing existing booking records.
ALTER TABLE bookings
    MODIFY COLUMN status ENUM(
        'pending',
        'confirmed',
        'in_progress',
        'completed',
        'cancelled',
        'no_show'
    ) NOT NULL DEFAULT 'pending';

-- History intentionally avoids a foreign key so it remains compatible with
-- both signed and unsigned user/booking IDs used by earlier project versions.
CREATE TABLE IF NOT EXISTS booking_status_history (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    booking_id INT UNSIGNED NOT NULL,
    from_status VARCHAR(30) DEFAULT NULL,
    to_status VARCHAR(30) NOT NULL,
    changed_by INT UNSIGNED DEFAULT NULL,
    note VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_booking_history_booking (booking_id, created_at),
    KEY idx_booking_history_actor (changed_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed a visible first timeline event for existing bookings.
INSERT INTO booking_status_history
    (booking_id, from_status, to_status, changed_by, note, created_at)
SELECT b.id, NULL, b.status, NULL, 'Existing booking imported into the timeline',
       COALESCE(b.created_at, CURRENT_TIMESTAMP)
FROM bookings b
WHERE NOT EXISTS (
    SELECT 1 FROM booking_status_history h WHERE h.booking_id = b.id
);
