<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;

final class DashboardController extends Controller
{
    public function index(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $this->render('dashboard/index', [
            'app' => $this->app,
            'user' => $this->app->session()->get((string) $this->app->config('auth.session_key', 'auth_user')),
            'success' => $this->app->session()->consumeFlash('success'),
            'error' => $this->app->session()->consumeFlash('error'),
        ]);
    }
}
