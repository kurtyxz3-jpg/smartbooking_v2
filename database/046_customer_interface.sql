USE smartbooking_v2;

-- One feedback record per customer appointment, allowing safe updates.
ALTER TABLE feedback
    ADD UNIQUE KEY IF NOT EXISTS uq_feedback_customer_booking (
        customer_id,
        booking_id
    );

-- Helpful appointment lookup index for the customer portal.
CREATE INDEX IF NOT EXISTS idx_bookings_customer_status_date
    ON bookings (customer_id, status, booking_date, booking_time);
