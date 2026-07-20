USE smartbooking_v2;

CREATE INDEX IF NOT EXISTS idx_payments_status_created
ON payments (status, created_at);

CREATE INDEX IF NOT EXISTS idx_bookings_status_date
ON bookings (status, booking_date);

CREATE INDEX IF NOT EXISTS idx_bookings_staff_date
ON bookings (staff_id, booking_date);

CREATE INDEX IF NOT EXISTS idx_users_role_status
ON users (role, status);
