<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Services\TaskService;

final class TaskController extends Controller
{
    public function index(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new TaskService($this->app);
        $user = $service->currentUser();
        $status = trim((string) $request->input('status', ''));

        $this->render('tasks/index', [
            'app' => $this->app,
            'user' => $user,
            'tasks' => $service->listTasks($user, ['status' => $status]),
            'statusCounts' => $service->statusCounts($user),
            'statuses' => TaskService::statuses(),
            'priorities' => TaskService::priorities(),
            'activeStatus' => $status,
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
                'department_id' => (string) $this->app->session()->consumeFlash('task_old_department_id', ''),
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
            $this->app->session()->flash('success', 'Task wurde erstellt.');
            $this->redirect('/tasks/' . $taskId);
            return;
        } catch (\RuntimeException $exception) {
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
        $user = $service->currentUser();
        $task = $service->findTask($user, (int) ($params['id'] ?? 0));

        if ($task === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        try {
            $service->updateTask($user, $task, [
                'department_id' => (int) $request->input('department_id', 0),
                'title' => (string) $request->input('title', ''),
                'description' => (string) $request->input('description', ''),
                'priority' => (string) $request->input('priority', 'normal'),
                'due_date' => (string) $request->input('due_date', ''),
                'assigned_to_user_id' => (int) $request->input('assigned_to_user_id', 0),
            ]);
            $this->app->session()->flash('success', 'Task wurde aktualisiert.');
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', 'Task konnte nicht aktualisiert werden.');
        }

        $this->redirect('/tasks/' . (int) $task['id']);
    }

    public function updateStatus(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new TaskService($this->app);
        $user = $service->currentUser();
        $task = $service->findTask($user, (int) ($params['id'] ?? 0));

        if ($task === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        try {
            $service->updateStatus($user, $task, (string) $request->input('status', ''));
            $this->app->session()->flash('success', 'Task-Status wurde aktualisiert.');
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', 'Task-Status konnte nicht aktualisiert werden.');
        }

        $this->redirect('/tasks/' . (int) $task['id']);
    }

    public function addComment(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new TaskService($this->app);
        $user = $service->currentUser();
        $task = $service->findTask($user, (int) ($params['id'] ?? 0));

        if ($task === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        try {
            $service->addComment($user, $task, (string) $request->input('body', ''));
            $this->app->session()->flash('success', 'Kommentar wurde hinzugefuegt.');
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', 'Kommentar konnte nicht gespeichert werden.');
        }

        $this->redirect('/tasks/' . (int) $task['id']);
    }
}
