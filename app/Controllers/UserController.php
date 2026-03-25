<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
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
            'defaultLeaderPassword' => UserService::DEFAULT_DEPARTMENT_LEADER_PASSWORD,
            'csrfToken' => CsrfMiddleware::token($this->app),
            'success' => $this->app->session()->consumeFlash('success'),
            'error' => $this->app->session()->consumeFlash('error'),
        ]);
    }

    public function resetPassword(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new UserService($this->app);
        $currentUser = $service->currentUser();

        if (!$service->isAdmin($currentUser)) {
            $this->app->response()->render('errors/403', [
                'app' => $this->app,
            ], 'app', 403);
            return;
        }

        try {
            $service->resetDepartmentLeaderPassword($currentUser, (int) ($params['id'] ?? 0));
            $this->app->session()->flash('success', 'Leiter-Passwort wurde zurueckgesetzt und muss beim naechsten Login geaendert werden.');
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', 'Leiter-Passwort konnte nicht zurueckgesetzt werden.');
        }

        $this->redirect('/users');
    }
}
