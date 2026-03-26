CREATE TABLE IF NOT EXISTS audit_filter_presets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    source VARCHAR(40) NULL,
    search VARCHAR(255) NULL,
    outcome VARCHAR(40) NULL,
    date_from DATE NULL,
    date_to DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_filter_presets_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    UNIQUE KEY uq_audit_filter_presets_user_name (user_id, name),
    INDEX idx_audit_filter_presets_user_id (user_id),
    INDEX idx_audit_filter_presets_user_updated_at (user_id, updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
