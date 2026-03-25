Verification performed for task workflow transitions:

- `php -l app/Services/TaskService.php`
- `php -l app/Controllers/TaskController.php`
- `php -l resources/views/tasks/show.php`
- `php -l tests/Unit/TaskServiceTest.php`
- `php tests/run.php`

Observed results:
- Syntax checks passed for all touched files.
- Test suite executed 18 tests with 0 failures.
- New unit coverage verifies allowed and rejected transitions for worker and manager scenarios.
- Task detail status form now renders only valid target states for the current user.
