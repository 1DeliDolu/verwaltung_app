Task integration now binds the existing `/tasks` module into the most relevant operational surfaces instead of creating a second workflow.

Implemented changes:
- Dashboard now shows a task summary block with status tiles and recent visible tasks.
- Department detail pages now show department-scoped task status tiles and recent tasks.
- Department pages link into `/tasks?department_id={id}` and `/tasks/create?department_id={id}`.
- Task list now accepts `department_id` filtering and exposes a department filter form.
- Task creation now preselects the department when opened from a department page.

Backend structure:
- `Task::visibleForUser()` now supports `department_id` and `limit`.
- `Task::countByStatusForUser()` now supports department-scoped counts.
- `TaskService` exposes filtered status counts and recent task retrieval.
- `DashboardController` and `DepartmentController` pass task summary data into their views.

User outcome:
- Users see cross-department tasks on the dashboard.
- Users see only that department's tasks inside each department page.
- Team leaders can jump straight into creating tasks for the current department.
