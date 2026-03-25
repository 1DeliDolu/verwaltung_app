Verification performed for admin leader assignment management:

- `php -l app/Models/User.php`
- `php -l app/Services/UserService.php`
- `php -l app/Controllers/UserController.php`
- `php -l routes/web.php`
- `php -l resources/views/users/index.php`
- `php -l tests/Feature/DatabaseWorkflowTest.php`
- `php tests/run.php`

Observed results:
- Syntax checks passed for all touched files.
- Full suite executed 31 tests with 0 failures.
- DB-backed integration coverage now verifies that an admin can:
  - move a `leiter.*` account to a new department
  - change membership role
  - leave exactly one department membership in place
  - keep `users.role_id` aligned with `department_user.membership_role`
