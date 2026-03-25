The admin user directory now supports a real lifecycle action for department leader accounts: password reset with forced password rotation.

Implemented changes:
- Added backend password reset support for leader accounts in the user model and service layer.
- Added admin-only `POST /users/{id}/reset-password`.
- Reused the existing default leader password as the reset target value.
- Reset now sets `password_change_required_at` and clears `password_changed_at`.
- Extended the `/users` table with password status badges and a reset action per leader account.

Behavior:
- Only admin users can trigger the reset action.
- Only accounts already present in the department leader directory are eligible.
- After reset, the leader must change the password on next login.

Why this shape:
- The current schema supports password lifecycle fields but does not support activation/deactivation.
- This change adds a real supported admin action instead of exposing unsupported lifecycle controls.
