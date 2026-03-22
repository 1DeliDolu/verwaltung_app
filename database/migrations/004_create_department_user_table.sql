CREATE TABLE IF NOT EXISTS department_user (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    department_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    membership_role ENUM('team_leader', 'employee') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_department_user (department_id, user_id),
    CONSTRAINT fk_department_user_department_id FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    CONSTRAINT fk_department_user_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
