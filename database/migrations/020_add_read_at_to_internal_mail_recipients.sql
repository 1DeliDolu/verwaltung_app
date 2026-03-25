ALTER TABLE internal_mail_recipients
    ADD COLUMN read_at TIMESTAMP NULL DEFAULT NULL AFTER created_at,
    ADD INDEX idx_internal_mail_recipients_read_at (recipient_user_id, read_at);
