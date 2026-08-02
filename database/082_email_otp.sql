USE smartbooking_v2;

ALTER TABLE email_verification_tokens
    ADD COLUMN IF NOT EXISTS verification_code_hash CHAR(64) DEFAULT NULL AFTER token_hash,
    ADD COLUMN IF NOT EXISTS attempt_count TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER verification_code_hash;
