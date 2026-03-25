The admin audit screen now supports date-range filtering and CSV export.

Implemented changes:
- Added `date_from` and `date_to` filtering to `AuditLogService::readAdminUserEvents()`.
- Added `adminUserEventsAsCsv()` for exporting the currently filtered dataset.
- Extended `/users/audit` so `format=csv` returns a CSV export instead of HTML.
- Updated the audit UI with:
  - date-from field
  - date-to field
  - CSV export button

Behavior:
- Date filters are applied inclusively:
  - `date_from` starts at `00:00:00`
  - `date_to` ends at `23:59:59`
- CSV export uses the same filters as the screen, so the downloaded file matches the visible dataset.
- Empty exports still return a valid CSV header row.
