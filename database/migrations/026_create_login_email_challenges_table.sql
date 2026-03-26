CREATE TABLE IF NOT EXISTS login_email_challenges (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    code_hash CHAR(64) NOT NULL,
    requested_ip VARCHAR(45) NOT NULL DEFAULT 'unknown',
    requested_user_agent VARCHAR(255) NULL,
    expires_at DATETIME NOT NULL,
    consumed_at DATETIME NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_login_email_challenges_user_id (user_id),
    KEY idx_login_email_challenges_expires_at (expires_at),
    KEY idx_login_email_challenges_consumed_at (consumed_at),
    CONSTRAINT fk_login_email_challenges_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
