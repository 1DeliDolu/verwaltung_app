The test suite now includes rollback-based database integration coverage for task, calendar, mail, and admin user workflows.

Implemented changes:
- Extended the test bootstrap to connect the shared PDO database instance for tests.
- Added transaction helpers to the base test case so DB-backed tests can create records safely and always roll back.
- Added seeded-user lookup helpers to avoid hardcoded ids in workflow tests.
- Added `DatabaseWorkflowTest` with real database assertions for:
  - task visibility by department membership
  - calendar visibility by department-bound event assignment
  - mail archive and restore behavior
  - admin leader password reset lifecycle fields

Why this matters:
- Earlier tests covered validation and route behavior but not actual persistence-layer authorization boundaries.
- These tests now prove that workflow rules still hold when real SQL reads and writes happen.

Safety model:
- Each DB-backed test runs inside a transaction and is rolled back after execution.
- Existing seed data is reused; no permanent fixture rows are left behind.
