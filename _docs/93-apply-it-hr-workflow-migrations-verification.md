## 93. Apply IT HR Workflow Migrations Verification

1. `mysql -h 127.0.0.1 -P 3306 -u root -pD0cker! verwaltung_app < database/migrations/018_link_employees_to_users_and_add_privacy_fields.sql` calistirildi.
2. `mysql -h 127.0.0.1 -P 3306 -u root -pD0cker! verwaltung_app < database/migrations/019_add_password_lifecycle_and_creator_to_users.sql` calistirildi.
3. `DESCRIBE employees;` sonucu icinde `user_id`, `data_processing_basis` ve `retention_until` alanlari dogrulandi.
4. `DESCRIBE users;` sonucu icinde `created_by_user_id`, `password_change_required_at` ve `password_changed_at` alanlari dogrulandi.
5. `SHOW CREATE TABLE employees` ile `fk_employees_user_id` foreign key'i ve `uq_employees_user_id` unique key'i dogrulandi.
6. `SHOW CREATE TABLE users` ile `fk_users_created_by_user_id` foreign key'i ve `idx_users_created_by_user_id` index'i dogrulandi.
