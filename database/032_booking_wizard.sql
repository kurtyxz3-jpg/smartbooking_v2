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
