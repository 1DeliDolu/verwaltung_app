ALTER TABLE users
    ADD COLUMN created_by_user_id BIGINT UNSIGNED NULL AFTER role_id,
    ADD COLUMN password_change_required_at TIMESTAMP NULL DEFAULT NULL AFTER created_by_user_id,
    ADD COLUMN password_changed_at TIMESTAMP NULL DEFAULT NULL AFTER password_change_required_at,
    ADD KEY idx_users_created_by_user_id (created_by_user_id),
    ADD CONSTRAINT fk_users_created_by_user_id FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL;
