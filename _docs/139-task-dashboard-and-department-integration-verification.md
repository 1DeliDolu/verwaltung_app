Verification performed for task integration:

- `php -l app/Models/Task.php`
- `php -l app/Services/TaskService.php`
- `php -l app/Controllers/DashboardController.php`
- `php -l app/Controllers/DepartmentController.php`
- `php -l app/Controllers/TaskController.php`
- `php -l resources/views/dashboard/index.php`
- `php -l resources/views/departments/show.php`
- `php -l resources/views/tasks/index.php`

Expected behavior after this change:
- `/dashboard` shows task status counts and recent tasks.
- `/departments/{slug}` shows only tasks from that department.
- `/tasks` can be filtered by department and status together.
- `/tasks/create?department_id={id}` preselects the given department.
