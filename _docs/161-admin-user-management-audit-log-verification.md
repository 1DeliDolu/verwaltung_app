Verification performed for admin user management audit logging:

- `php -l app/Services/AuditLogService.php`
- `php -l app/Services/UserService.php`
- `php -l app/Controllers/UserController.php`
- `php -l tests/Unit/AuditLogServiceTest.php`
- `php tests/run.php`

Observed results:
- Syntax checks passed for all touched files.
- Full suite executed 33 tests with 0 failures.
- Unit coverage now verifies that admin user-management audit entries are written with:
  - `event = admin_user_management`
  - action name
  - actor
  - target user
  - department
  - metadata payload
