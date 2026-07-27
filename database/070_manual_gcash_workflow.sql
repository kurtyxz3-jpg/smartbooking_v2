USE smartbooking_v2;

ALTER TABLE branches
    ADD COLUMN IF NOT EXISTS gcash_merchant_name VARCHAR(150) NULL AFTER closing_time,
    ADD COLUMN IF NOT EXISTS gcash_number VARCHAR(30) NULL AFTER gcash_merchant_name,
    ADD COLUMN IF NOT EXISTS gcash_qr_path VARCHAR(255) NULL AFTER gcash_number;

ALTER TABLE bookings
    ADD COLUMN IF NOT EXISTS payment_due_at DATETIME NULL AFTER branch_id,
    ADD INDEX IF NOT EXISTS idx_bookings_payment_due (status, payment_due_at);

ALTER TABLE payments
    ADD COLUMN IF NOT EXISTS submitted_at DATETIME NULL AFTER status,
    ADD COLUMN IF NOT EXISTS verified_at DATETIME NULL AFTER submitted_at,
    ADD COLUMN IF NOT EXISTS verified_by INT UNSIGNED NULL AFTER verified_at,
    ADD COLUMN IF NOT EXISTS review_note VARCHAR(500) NULL AFTER verified_by,
    ADD INDEX IF NOT EXISTS idx_payments_reference (reference_number),
    ADD INDEX IF NOT EXISTS idx_payments_verifier (verified_by);

UPDATE payments
SET submitted_at = created_at
WHERE submitted_at IS NULL;

UPDATE bookings
SET payment_due_at = DATE_ADD(created_at, INTERVAL 30 MINUTE)
WHERE status = 'pending'
  AND payment_due_at IS NULL;
