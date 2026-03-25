Admin user-management audit logs are now visible inside the application.

Implemented changes:
- Added `AuditLogService::readAdminUserEvents()` to read and filter JSON-lines admin audit entries.
- Added admin-only `/users/audit` route and screen.
- Linked the new screen from the admin header navigation.
- Added server-side filters for:
  - free-text search
  - action
  - outcome

Screen behavior:
- displays newest audit entries first
- shows actor, target user, department, membership role, timestamp, and reason
- handles the case where no audit log file exists yet by rendering an empty state

Result:
- Admins can now inspect password reset and assignment changes without leaving the application or reading raw log files directly.
