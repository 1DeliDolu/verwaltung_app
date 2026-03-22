CREATE TABLE IF NOT EXISTS internal_mail_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mail_id BIGINT UNSIGNED NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(255) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    file_content LONGBLOB NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_internal_mail_attachments_mail
        FOREIGN KEY (mail_id) REFERENCES internal_mails(id)
        ON DELETE CASCADE,
    INDEX idx_internal_mail_attachments_mail_id (mail_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
