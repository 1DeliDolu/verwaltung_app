<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Services\DepartmentService;

final class DashboardController extends Controller
{
    public function index(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        $departmentService = new DepartmentService($this->app);

        $this->render('dashboard/index', [
            'app' => $this->app,
            'user' => $departmentService->currentUser(),
            'departments' => $departmentService->dashboardDepartments(),
            'success' => $this->app->session()->consumeFlash('success'),
            'error' => $this->app->session()->consumeFlash('error'),
        ]);
    }
}
