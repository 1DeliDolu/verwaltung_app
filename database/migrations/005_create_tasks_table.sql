CREATE TABLE IF NOT EXISTS tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    department_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'open',
    priority VARCHAR(40) NOT NULL DEFAULT 'normal',
    due_date DATE DEFAULT NULL,
    created_by_user_id BIGINT UNSIGNED NOT NULL,
    assigned_to_user_id BIGINT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_tasks_department
        FOREIGN KEY (department_id) REFERENCES departments(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_tasks_creator
        FOREIGN KEY (created_by_user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_tasks_assignee
        FOREIGN KEY (assigned_to_user_id) REFERENCES users(id)
        ON DELETE SET NULL,
    INDEX idx_tasks_department_status (department_id, status),
    INDEX idx_tasks_assignee (assigned_to_user_id),
    INDEX idx_tasks_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
