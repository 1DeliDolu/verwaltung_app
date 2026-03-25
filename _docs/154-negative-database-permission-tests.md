The database-backed workflow test layer now covers negative permission paths in addition to positive scenarios.

Implemented changes:
- Extended `DatabaseWorkflowTest` with real persistence-backed denial cases for:
  - forbidden task status transitions by a non-manager worker
  - foreign department leader completing another department's calendar event
  - non-admin password reset attempts against leader accounts
  - unrelated users trying to restore archived mail

Why this matters:
- Positive-path tests alone do not prove least-privilege behavior.
- These new checks verify that service-layer authorization rules still hold when real database state exists.

Covered denial cases:
- assigned worker cannot reopen a finished task
- HR leader cannot complete an IT-created event
- non-admin leader cannot reset another leader's password
- foreign user cannot restore mail they do not own as sender or recipient
