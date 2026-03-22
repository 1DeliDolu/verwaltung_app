CREATE TABLE IF NOT EXISTS internal_mail_recipients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mail_id BIGINT UNSIGNED NOT NULL,
    recipient_user_id BIGINT UNSIGNED NOT NULL,
    recipient_name VARCHAR(255) NOT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_internal_mail_recipients_mail
        FOREIGN KEY (mail_id) REFERENCES internal_mails(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_internal_mail_recipients_user
        FOREIGN KEY (recipient_user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    UNIQUE KEY uniq_internal_mail_recipient (mail_id, recipient_user_id),
    INDEX idx_internal_mail_recipients_user_id (recipient_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
