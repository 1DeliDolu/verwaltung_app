Verification performed for negative database permission tests:

- `php -l tests/Feature/DatabaseWorkflowTest.php`
- `php tests/run.php`

Observed results:
- Syntax check passed for `DatabaseWorkflowTest`.
- Full suite executed 30 tests with 0 failures.
- Negative DB-backed permission tests now verify denial behavior for task, calendar, mail, and admin password reset flows.
