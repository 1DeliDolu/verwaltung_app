Verification performed for calendar authorization hardening:

- `php -l app/Services/CalendarService.php`
- `php -l app/Controllers/CalendarController.php`
- `php -l resources/views/pages/calendar.php`
- `php -l tests/run.php`
- `php -l tests/Unit/CalendarServiceTest.php`
- `php tests/run.php`

Observed results:
- Syntax checks passed for all touched files.
- Test suite executed 14 tests with 0 failures.
- Calendar authorization logic is now covered by unit tests for admin, owner, and foreign employee cases.
