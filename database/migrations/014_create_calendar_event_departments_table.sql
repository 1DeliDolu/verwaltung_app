CREATE TABLE IF NOT EXISTS calendar_event_departments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    calendar_event_id BIGINT UNSIGNED NOT NULL,
    department_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_calendar_event_departments_event
        FOREIGN KEY (calendar_event_id) REFERENCES calendar_events(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_calendar_event_departments_department
        FOREIGN KEY (department_id) REFERENCES departments(id)
        ON DELETE CASCADE,
    UNIQUE KEY uniq_calendar_event_department (calendar_event_id, department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
