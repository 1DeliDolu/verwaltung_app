ALTER TABLE internal_mails
    ADD COLUMN sender_archived_at TIMESTAMP NULL DEFAULT NULL AFTER updated_at,
    ADD INDEX idx_internal_mails_sender_archived_at (sender_id, sender_archived_at);

ALTER TABLE internal_mail_recipients
    ADD COLUMN archived_at TIMESTAMP NULL DEFAULT NULL AFTER read_at,
    ADD INDEX idx_internal_mail_recipients_archived_at (recipient_user_id, archived_at);
