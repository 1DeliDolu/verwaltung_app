Admin user-management actions are now written to a dedicated audit log.

Implemented changes:
- Extended `AuditLogService` with `recordAdminUserEvent()`.
- Added a dedicated log target: `storage/logs/admin-user-management.log`.
- Wired admin password reset and assignment update actions in `UserController` to record:
  - actor
  - target user
  - target department where relevant
  - metadata such as membership role or default-password reset
  - success or failure outcome
  - failure reason when available

Why this matters:
- Admin lifecycle actions change access boundaries and credentials.
- These operations need an audit trail separate from personnel-document access logging.

Result:
- `/users/{id}/reset-password` and `/users/{id}/assignment` now leave JSON-lines audit entries.
- The logging path remains file-based and does not require a schema migration.
