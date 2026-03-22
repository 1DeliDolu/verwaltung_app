<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\CsrfMiddleware;
use App\Services\AuthService;

final class AuthController extends Controller
{
    public function showLogin(Request $request): void
    {
        if ($this->app->session()->get($this->app->config('auth.session_key')) !== null) {
            $this->redirect('/dashboard');
        }

        $token = CsrfMiddleware::token($this->app);

        $this->render('auth/login', [
            'app' => $this->app,
            'csrfToken' => $token,
            'old' => [
                'email' => (string) $this->app->session()->consumeFlash('old_email', ''),
            ],
            'error' => $this->app->session()->consumeFlash('error'),
            'success' => $this->app->session()->consumeFlash('success'),
        ]);
    }

    public function login(Request $request): void
    {
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $credentials = [
            'email' => trim((string) $request->input('email', '')),
            'password' => (string) $request->input('password', ''),
        ];

        $service = new AuthService($this->app);

        if (!$service->attempt($credentials['email'], $credentials['password'])) {
            $this->app->session()->flash('error', 'E-Mail oder Passwort ist ungueltig.');
            $this->app->session()->flash('old_email', $credentials['email']);
            $this->redirect('/login');
        }

        $this->app->session()->flash('success', 'Anmeldung erfolgreich.');
        $this->redirect('/dashboard');
    }

    public function logout(Request $request): void
    {
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new AuthService($this->app);
        $service->logout();

        $this->app->session()->flash('success', 'Abmeldung erfolgreich.');
        $this->redirect('/login');
    }
}
