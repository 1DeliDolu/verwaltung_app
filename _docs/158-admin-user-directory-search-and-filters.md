The admin `/users` directory now supports server-side search and filtering for managed `leiter.*` accounts.

Implemented changes:
- Added `search`, `department`, and `membership_role` filters to the user directory service.
- Wired filter parsing into `UserController::index()`.
- Added a responsive filter form above the directory table.
- Kept filtering on the server side so the rendered dataset is already narrowed before display.

Supported filters:
- free-text search across name, email, department name, and department slug
- department filter by slug
- membership-role filter (`team_leader` or `employee`)

User outcome:
- Admins can quickly narrow large leader lists by typing a name or email fragment.
- Reassigned `leiter.*` accounts can be filtered by current department and current membership role.
- Table counts now reflect the filtered result set rather than the full dataset.
