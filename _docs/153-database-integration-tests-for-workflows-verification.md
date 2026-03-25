Verification performed for database integration workflow tests:

- `php -l tests/bootstrap.php`
- `php -l tests/TestCase.php`
- `php -l tests/Feature/DatabaseWorkflowTest.php`
- `php tests/run.php`

Observed results:
- Syntax checks passed for all touched files.
- The test suite executed 27 tests with 0 failures.
- DB-backed tests verified:
  - IT task visibility stays hidden from HR
  - IT-bound calendar events stay hidden from HR
  - archived mail can be restored
  - admin password reset sets `password_change_required_at` and clears `password_changed_at`
