Calendar visibility is now scoped to authenticated users and their department context.

Implemented changes:
- `/calendar` now requires authentication on the index route as well.
- `CalendarService::upcomingEvents()` now resolves the current user and visible department ids.
- `CalendarEvent` query logic now filters non-admin users to:
  - events they created
  - events without any department assignment
  - events assigned to at least one visible department
- Admin users still see the full calendar.

Reasoning:
- Calendar entries are operational data and should not be globally visible.
- Visibility is enforced in the backend query layer instead of relying on presentation-only hiding.
- Events without department assignment remain globally visible to authenticated users as general entries.

User outcome:
- Anonymous visitors are redirected to login before viewing the calendar.
- Department members see only relevant department events plus general entries.
- Event creators keep visibility to their own entries.
