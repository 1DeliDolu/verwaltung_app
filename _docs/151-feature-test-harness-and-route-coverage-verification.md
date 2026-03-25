Verification performed for feature test harness and route coverage:

- `php -l app/Core/RedirectException.php`
- `php -l app/Core/Response.php`
- `php -l tests/bootstrap.php`
- `php -l tests/TestCase.php`
- `php -l tests/run.php`
- `php -l tests/Feature/AuthenticationTest.php`
- `php -l tests/Feature/DepartmentPagesTest.php`
- `php -l tests/Feature/TaskWorkflowTest.php`
- `php tests/run.php`

Observed results:
- Syntax checks passed for all touched files.
- The custom runner executed 24 tests with 0 failures.
- Feature tests now cover redirect, access-control, and 404 behavior using the real route table.
