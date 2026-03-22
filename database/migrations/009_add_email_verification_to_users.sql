ALTER TABLE users
    ADD COLUMN email_verified_at TIMESTAMP NULL DEFAULT NULL AFTER role_id,
    ADD COLUMN email_verification_token VARCHAR(64) NULL AFTER email_verified_at,
    ADD COLUMN email_verification_sent_at TIMESTAMP NULL DEFAULT NULL AFTER email_verification_token;
