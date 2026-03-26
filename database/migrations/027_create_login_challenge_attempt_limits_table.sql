CREATE TABLE IF NOT EXISTS login_challenge_attempt_limits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    throttle_key CHAR(64) NOT NULL,
    challenge_id BIGINT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    failed_attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    window_started_at DATETIME NOT NULL,
    last_attempted_at DATETIME NOT NULL,
    locked_until DATETIME NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_login_challenge_attempt_limits_throttle_key (throttle_key),
    KEY idx_login_challenge_attempt_limits_challenge_id (challenge_id),
    KEY idx_login_challenge_attempt_limits_locked_until (locked_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
