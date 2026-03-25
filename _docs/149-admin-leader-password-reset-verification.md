Verification performed for admin leader password reset:

- `php -l app/Models/User.php`
- `php -l app/Services/UserService.php`
- `php -l app/Controllers/UserController.php`
- `php -l routes/web.php`
- `php -l resources/views/users/index.php`
- `php tests/run.php`

Observed results:
- Syntax checks passed for all touched files.
- Test suite executed 18 tests with 0 failures.
- `/users` now shows password lifecycle state per leader account.
- Admins can reset a leader password to the default value and force a password change on next login.
