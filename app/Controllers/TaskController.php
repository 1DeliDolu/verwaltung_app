<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Services\AuditLogService;
use App\Services\TaskService;

final class TaskController extends Controller
{
    public function audit(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new TaskService($this->app);
        $audit = new AuditLogService($this->app);
        $user = $service->currentUser();
        $departments = $service->visibleDepartments($user);
        $visibleDepartmentIds = array_map(
            static fn (array $department): int => (int) $department['id'],
            $departments
        );

        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'department_id' => (int) $request->input('department_id', 0),
            'action' => trim((string) $request->input('action', '')),
            'outcome' => trim((string) $request->input('outcome', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        if (!$service->isAdmin($user) && $filters['department_id'] > 0 && !in_array($filters['department_id'], $visibleDepartmentIds, true)) {
            $filters['department_id'] = 0;
        }

        $events = array_values(array_filter(
            $audit->readTaskWorkflowEvents($filters),
            static function (array $event) use ($service, $user, $visibleDepartmentIds): bool {
                if ($service->isAdmin($user)) {
                    return true;
                }

                return in_array((int) ($event['department']['id'] ?? 0), $visibleDepartmentIds, true);
            }
        ));

        if ((string) $request->input('format', '') === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="task-workflow-audit.csv"');
            echo $audit->taskWorkflowEventsAsCsv($events);
            return;
        }

        $this->render('tasks/audit', [
            'app' => $this->app,
            'user' => $user,
            'events' => $events,
            'filters' => $filters,
            'departments' => $departments,
            'actionOptions' => [
                'create_task' => 'Task erstellt',
                'update_task' => 'Task bearbeitet',
                'update_status' => 'Status geaendert',
                'add_comment' => 'Kommentar',
            ],
            'outcomeOptions' => [
                'success' => 'Erfolg',
                'failure' => 'Fehler',
            ],
        ]);
    }

    public function index(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new TaskService($this->app);
        $user = $service->currentUser();
        $status = trim((string) $request->input('status', ''));
        $departmentId = (int) $request->input('department_id', 0);

        $this->render('tasks/index', [
            'app' => $this->app,
            'user' => $user,
            'tasks' => $service->listTasks($user, ['status' => $status, 'department_id' => $departmentId]),
            'statusCounts' => $service->statusCounts($user, ['department_id' => $departmentId]),
            'statuses' => TaskService::statuses(),
            'priorities' => TaskService::priorities(),
            'activeStatus' => $status,
            'activeDepartmentId' => $departmentId,
            'departments' => $service->visibleDepartments($user),
            'success' => $this->app->session()->consumeFlash('success'),
            'error' => $this->app->session()->consumeFlash('error'),
        ]);
    }

    public function create(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new TaskService($this->app);
        $user = $service->currentUser();

        $this->render('tasks/create', [
            'app' => $this->app,
            'user' => $user,
            'departments' => $service->visibleDepartments($user),
            'assignableUsersMap' => $service->assignableUsersMap($user),
            'priorities' => TaskService::priorities(),
            'csrfToken' => CsrfMiddleware::token($this->app),
            'old' => [
                'department_id' => (string) $this->app->session()->consumeFlash('task_old_department_id', (string) (int) $request->input('department_id', 0)),
                'title' => (string) $this->app->session()->consumeFlash('task_old_title', ''),
                'description' => (string) $this->app->session()->consumeFlash('task_old_description', ''),
                'priority' => (string) $this->app->session()->consumeFlash('task_old_priority', 'normal'),
                'due_date' => (string) $this->app->session()->consumeFlash('task_old_due_date', ''),
                'assigned_to_user_id' => (string) $this->app->session()->consumeFlash('task_old_assigned_to_user_id', ''),
            ],
        ]);
    }

    public function store(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new TaskService($this->app);
        $audit = new AuditLogService($this->app);
        $user = $service->currentUser();
        $payload = [
            'department_id' => (int) $request->input('department_id', 0),
            'title' => (string) $request->input('title', ''),
            'description' => (string) $request->input('description', ''),
            'priority' => (string) $request->input('priority', 'normal'),
            'due_date' => (string) $request->input('due_date', ''),
            'assigned_to_user_id' => (int) $request->input('assigned_to_user_id', 0),
        ];

        try {
            $taskId = $service->createTask($user, $payload);
            $task = $service->findTask($user, $taskId);
            $audit->recordTaskWorkflowEvent('create_task', [
                'actor' => $user,
                'task' => $task ?? ['id' => $taskId, 'title' => $payload['title'], 'status' => 'open', 'priority' => $payload['priority']],
                'department' => $task !== null
                    ? [
                        'id' => (int) ($task['department_id'] ?? 0),
                        'slug' => (string) ($task['department_slug'] ?? ''),
                        'name' => (string) ($task['department_name'] ?? ''),
                    ]
                    : ['id' => $payload['department_id']],
                'metadata' => [
                    'priority' => $payload['priority'],
                    'assigned_to_user_id' => $payload['assigned_to_user_id'],
                    'due_date' => $payload['due_date'],
                    'status_to' => 'open',
                ],
            ]);
            $this->app->session()->flash('success', 'Task wurde erstellt.');
            $this->redirect('/tasks/' . $taskId);
            return;
        } catch (\RuntimeException $exception) {
            $audit->recordTaskWorkflowEvent('create_task', [
                'actor' => $user,
                'task' => ['title' => $payload['title'], 'priority' => $payload['priority']],
                'department' => ['id' => $payload['department_id']],
                'metadata' => [
                    'priority' => $payload['priority'],
                    'assigned_to_user_id' => $payload['assigned_to_user_id'],
                    'due_date' => $payload['due_date'],
                ],
                'outcome' => 'failure',
                'reason' => $exception->getMessage(),
            ]);
            $this->app->session()->flash('error', 'Task konnte nicht erstellt werden.');
            $this->app->session()->flash('task_old_department_id', (string) $payload['department_id']);
            $this->app->session()->flash('task_old_title', $payload['title']);
            $this->app->session()->flash('task_old_description', $payload['description']);
            $this->app->session()->flash('task_old_priority', $payload['priority']);
            $this->app->session()->flash('task_old_due_date', $payload['due_date']);
            $this->app->session()->flash('task_old_assigned_to_user_id', (string) $payload['assigned_to_user_id']);
        }

        $this->redirect('/tasks/create');
    }

    public function show(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new TaskService($this->app);
        $audit = new AuditLogService($this->app);
        $user = $service->currentUser();
        $task = $service->findTask($user, (int) ($params['id'] ?? 0));

        if ($task === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        $this->render('tasks/show', [
            'app' => $this->app,
            'user' => $user,
            'task' => $task,
            'comments' => $service->commentsForTask($task),
            'statuses' => TaskService::statuses(),
            'availableStatuses' => $service->availableStatuses($user, $task),
            'priorities' => TaskService::priorities(),
            'canManage' => $service->mayManageTask($user, $task),
            'canWork' => $service->mayWorkOnTask($user, $task),
            'csrfToken' => CsrfMiddleware::token($this->app),
            'success' => $this->app->session()->consumeFlash('success'),
            'error' => $this->app->session()->consumeFlash('error'),
        ]);
    }

    public function edit(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new TaskService($this->app);
        $user = $service->currentUser();
        $task = $service->findTask($user, (int) ($params['id'] ?? 0));

        if ($task === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        if (!$service->mayManageTask($user, $task)) {
            $this->app->response()->render('errors/403', ['app' => $this->app], 'app', 403);
            return;
        }

        $this->render('tasks/edit', [
            'app' => $this->app,
            'user' => $user,
            'task' => $task,
            'departments' => $service->visibleDepartments($user),
            'assignableUsersMap' => $service->assignableUsersMap($user),
            'priorities' => TaskService::priorities(),
            'csrfToken' => CsrfMiddleware::token($this->app),
        ]);
    }

    public function update(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new TaskService($this->app);
        $audit = new AuditLogService($this->app);
        $user = $service->currentUser();
        $task = $service->findTask($user, (int) ($params['id'] ?? 0));

        if ($task === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        try {
            $payload = [
                'department_id' => (int) $request->input('department_id', 0),
                'title' => (string) $request->input('title', ''),
                'description' => (string) $request->input('description', ''),
                'priority' => (string) $request->input('priority', 'normal'),
                'due_date' => (string) $request->input('due_date', ''),
                'assigned_to_user_id' => (int) $request->input('assigned_to_user_id', 0),
            ];
            $service->updateTask($user, $task, [
                'department_id' => $payload['department_id'],
                'title' => $payload['title'],
                'description' => $payload['description'],
                'priority' => $payload['priority'],
                'due_date' => $payload['due_date'],
                'assigned_to_user_id' => $payload['assigned_to_user_id'],
            ]);
            $updatedTask = $service->findTask($user, (int) $task['id']);
            $audit->recordTaskWorkflowEvent('update_task', [
                'actor' => $user,
                'task' => $updatedTask ?? $task,
                'department' => $updatedTask !== null
                    ? [
                        'id' => (int) ($updatedTask['department_id'] ?? 0),
                        'slug' => (string) ($updatedTask['department_slug'] ?? ''),
                        'name' => (string) ($updatedTask['department_name'] ?? ''),
                    ]
                    : [
                        'id' => (int) ($task['department_id'] ?? 0),
                        'slug' => (string) ($task['department_slug'] ?? ''),
                        'name' => (string) ($task['department_name'] ?? ''),
                    ],
                'metadata' => [
                    'priority' => $payload['priority'],
                    'assigned_to_user_id' => $payload['assigned_to_user_id'],
                    'due_date' => $payload['due_date'],
                ],
            ]);
            $this->app->session()->flash('success', 'Task wurde aktualisiert.');
        } catch (\RuntimeException $exception) {
            $audit->recordTaskWorkflowEvent('update_task', [
                'actor' => $user,
                'task' => $task,
                'department' => [
                    'id' => (int) ($task['department_id'] ?? 0),
                    'slug' => (string) ($task['department_slug'] ?? ''),
                    'name' => (string) ($task['department_name'] ?? ''),
                ],
                'outcome' => 'failure',
                'reason' => $exception->getMessage(),
            ]);
            $this->app->session()->flash('error', 'Task konnte nicht aktualisiert werden.');
        }

        $this->redirect('/tasks/' . (int) $task['id']);
    }

    public function updateStatus(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new TaskService($this->app);
        $audit = new AuditLogService($this->app);
        $user = $service->currentUser();
        $task = $service->findTask($user, (int) ($params['id'] ?? 0));

        if ($task === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        try {
            $nextStatus = (string) $request->input('status', '');
            $service->updateStatus($user, $task, $nextStatus);
            $updatedTask = $service->findTask($user, (int) $task['id']);
            $audit->recordTaskWorkflowEvent('update_status', [
                'actor' => $user,
                'task' => $updatedTask ?? $task,
                'department' => [
                    'id' => (int) ($task['department_id'] ?? 0),
                    'slug' => (string) ($task['department_slug'] ?? ''),
                    'name' => (string) ($task['department_name'] ?? ''),
                ],
                'metadata' => [
                    'status_from' => (string) ($task['status'] ?? ''),
                    'status_to' => $nextStatus,
                ],
            ]);
            $this->app->session()->flash('success', 'Task-Status wurde aktualisiert.');
        } catch (\RuntimeException $exception) {
            $audit->recordTaskWorkflowEvent('update_status', [
                'actor' => $user,
                'task' => $task,
                'department' => [
                    'id' => (int) ($task['department_id'] ?? 0),
                    'slug' => (string) ($task['department_slug'] ?? ''),
                    'name' => (string) ($task['department_name'] ?? ''),
                ],
                'metadata' => [
                    'status_from' => (string) ($task['status'] ?? ''),
                    'status_to' => (string) $request->input('status', ''),
                ],
                'outcome' => 'failure',
                'reason' => $exception->getMessage(),
            ]);
            $this->app->session()->flash('error', 'Task-Status konnte nicht aktualisiert werden.');
        }

        $this->redirect('/tasks/' . (int) $task['id']);
    }

    public function addComment(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new TaskService($this->app);
        $audit = new AuditLogService($this->app);
        $user = $service->currentUser();
        $task = $service->findTask($user, (int) ($params['id'] ?? 0));

        if ($task === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        try {
            $body = (string) $request->input('body', '');
            $service->addComment($user, $task, $body);
            $audit->recordTaskWorkflowEvent('add_comment', [
                'actor' => $user,
                'task' => $task,
                'department' => [
                    'id' => (int) ($task['department_id'] ?? 0),
                    'slug' => (string) ($task['department_slug'] ?? ''),
                    'name' => (string) ($task['department_name'] ?? ''),
                ],
                'metadata' => [
                    'comment_preview' => mb_strimwidth(trim($body), 0, 160, '...'),
                ],
            ]);
            $this->app->session()->flash('success', 'Kommentar wurde hinzugefuegt.');
        } catch (\RuntimeException $exception) {
            $audit->recordTaskWorkflowEvent('add_comment', [
                'actor' => $user,
                'task' => $task,
                'department' => [
                    'id' => (int) ($task['department_id'] ?? 0),
                    'slug' => (string) ($task['department_slug'] ?? ''),
                    'name' => (string) ($task['department_name'] ?? ''),
                ],
                'metadata' => [
                    'comment_preview' => mb_strimwidth(trim((string) $request->input('body', '')), 0, 160, '...'),
                ],
                'outcome' => 'failure',
                'reason' => $exception->getMessage(),
            ]);
            $this->app->session()->flash('error', 'Kommentar konnte nicht gespeichert werden.');
        }

        $this->redirect('/tasks/' . (int) $task['id']);
    }
}
