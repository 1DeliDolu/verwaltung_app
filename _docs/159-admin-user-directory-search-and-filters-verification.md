Verification performed for admin user directory filtering:

- `php -l app/Services/UserService.php`
- `php -l app/Controllers/UserController.php`
- `php -l resources/views/users/index.php`
- `php -l tests/Feature/DatabaseWorkflowTest.php`
- `php tests/run.php`

Observed results:
- Syntax checks passed for all touched files.
- Full suite executed 32 tests with 0 failures.
- DB-backed integration coverage now verifies that combined search + department + membership filters narrow the admin user directory as expected.
