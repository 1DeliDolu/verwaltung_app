Verification performed for the admin audit screen:

- `php -l app/Services/AuditLogService.php`
- `php -l app/Controllers/UserController.php`
- `php -l resources/views/users/audit.php`
- `php -l tests/Unit/AuditLogServiceTest.php`
- `php -l tests/Feature/AuthenticationTest.php`
- `php tests/run.php`

Observed results:
- Syntax checks passed for all touched files.
- Full suite executed 35 tests with 0 failures.
- Unit coverage now verifies reading and filtering admin audit entries.
- Feature coverage now verifies that an authenticated admin can open `/users/audit`.
