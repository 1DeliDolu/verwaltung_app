Verification performed for calendar department visibility:

- `php -l app/Models/CalendarEvent.php`
- `php -l app/Services/CalendarService.php`
- `php -l app/Controllers/CalendarController.php`
- `php tests/run.php`

Observed results:
- Syntax checks passed for all touched files.
- Test suite executed 14 tests with 0 failures.
- Calendar index now requires authentication before rendering.
- Non-admin visibility is constrained in the SQL query based on creator ownership and visible departments.
