CREATE TABLE IF NOT EXISTS infrastructure_services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    service_type ENUM('mail', 'file', 'other') NOT NULL DEFAULT 'other',
    host_name VARCHAR(160) NOT NULL,
    status ENUM('planned', 'active', 'maintenance') NOT NULL DEFAULT 'planned',
    access_level VARCHAR(120) NOT NULL,
    description TEXT NULL,
    managed_by_department_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_infrastructure_services_department_id FOREIGN KEY (managed_by_department_id) REFERENCES departments(id) ON DELETE SET NULL
);
