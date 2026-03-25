<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Services\UserService;

final class UserController extends Controller
{
    public function index(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new UserService($this->app);
        $currentUser = $service->currentUser();

        if (!$service->isAdmin($currentUser)) {
            $this->app->response()->render('errors/403', [
                'app' => $this->app,
            ], 'app', 403);
            return;
        }

        $this->render('users/index', [
            'app' => $this->app,
            'user' => $currentUser,
            'leaders' => $service->departmentLeaderDirectory(),
            'defaultLeaderPassword' => 'DockerDocker!123',
        ]);
    }
}
