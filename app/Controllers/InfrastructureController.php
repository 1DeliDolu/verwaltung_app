<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Services\DepartmentService;
use App\Services\InfrastructureService;

final class InfrastructureController extends Controller
{
    public function index(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new InfrastructureService($this->app);

        $this->render('services/index', [
            'app' => $this->app,
            'services' => $service->all(),
        ]);
    }

    public function fileBrowser(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $infrastructureService = new InfrastructureService($this->app);
        $departmentService = new DepartmentService($this->app);

        $this->render('services/fileserver', [
            'app' => $this->app,
            'user' => $departmentService->currentUser(),
            'shares' => $infrastructureService->departmentFileBrowser($departmentService),
        ]);
    }
}
