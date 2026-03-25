The admin user directory now supports department and membership-role reassignment for `leiter.*` accounts.

Implemented changes:
- Added backend support to replace a user's department membership and synchronize the user role.
- Added admin-only `POST /users/{id}/assignment`.
- Expanded the `/users` screen so each managed leader account can be reassigned to:
  - a different department
  - `team_leader` or `employee` membership role
- Broadened the directory filter to keep all `leiter.*` accounts visible even if they are demoted from `team_leader`.

Behavior:
- Assignment changes are applied in a transaction.
- Existing department memberships for the target account are replaced so only one active assignment remains.
- The `users.role_id` value is kept in sync with the selected membership role.

Why this shape:
- The current data model stores role and department context in separate places.
- Admin changes need to update both layers together or the account becomes inconsistent.
