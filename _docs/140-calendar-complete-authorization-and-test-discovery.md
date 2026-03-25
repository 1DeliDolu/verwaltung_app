Calendar completion now follows the same authorization boundary as calendar edit and delete actions.

Implemented changes:
- Added `CalendarService::completeEvent()` so completion is checked in the service layer.
- Reused existing event-management authorization before marking an event complete.
- Updated the calendar controller to handle unauthorized completion attempts safely.
- Hid the `Erledigt` button in the calendar UI for users who may not manage the event.

Test infrastructure improvement:
- `tests/run.php` now auto-discovers all `Unit/*Test.php` and `Feature/*Test.php` files.
- Added a unit test file for calendar event management authorization.

User outcome:
- Only the event creator or an admin can complete an event.
- Unauthorized users no longer see completion controls for foreign events.
- New tests are not silently skipped by the default test runner anymore.
