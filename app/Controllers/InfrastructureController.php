<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\VerifiedMiddleware;
use App\Services\InfrastructureService;

final class InfrastructureController extends Controller
{
    public function index(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        VerifiedMiddleware::handle($this->app);

        $service = new InfrastructureService();

        $this->render('services/index', [
            'app' => $this->app,
            'services' => $service->all(),
        ]);
    }
}
