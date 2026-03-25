<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Services\DepartmentService;
use App\Services\TaskService;

final class DashboardController extends Controller
{
    public function index(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        $departmentService = new DepartmentService($this->app);
        $taskService = new TaskService($this->app);
        $user = $departmentService->currentUser();

        $this->render('dashboard/index', [
            'app' => $this->app,
            'user' => $user,
            'departments' => $departmentService->dashboardDepartments(),
            'taskStatusCounts' => $taskService->statusCounts($user),
            'recentTasks' => $taskService->recentTasks($user, [], 4),
            'taskStatuses' => TaskService::statuses(),
            'taskPriorities' => TaskService::priorities(),
            'success' => $this->app->session()->consumeFlash('success'),
            'error' => $this->app->session()->consumeFlash('error'),
        ]);
    }
}
