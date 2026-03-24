ALTER TABLE employees
    ADD COLUMN user_id BIGINT UNSIGNED NULL AFTER department_id,
    ADD COLUMN data_processing_basis VARCHAR(180) NULL AFTER notes,
    ADD COLUMN retention_until DATE NULL AFTER data_processing_basis,
    ADD UNIQUE KEY uq_employees_user_id (user_id),
    ADD KEY idx_employees_user_id (user_id),
    ADD CONSTRAINT fk_employees_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
