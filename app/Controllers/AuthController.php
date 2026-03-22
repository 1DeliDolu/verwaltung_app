<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\CsrfMiddleware;
use App\Services\AuthService;
use App\Services\EmailVerificationService;

final class AuthController extends Controller
{
    public function showLogin(Request $request, array $params = []): void
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

    public function login(Request $request, array $params = []): void
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

        $user = $this->app->session()->get((string) $this->app->config('auth.session_key', 'auth_user'));

        if (($user['email_verified_at'] ?? null) === null) {
            try {
                (new EmailVerificationService($this->app))->sendVerificationMail($user);
                $this->app->session()->flash('success', 'Bitte bestaetige deine E-Mail-Adresse. Eine Verifizierungs-E-Mail wurde gesendet.');
            } catch (\RuntimeException $exception) {
                $this->app->session()->flash('error', 'Anmeldung erfolgreich, aber die Verifizierungs-E-Mail konnte nicht gesendet werden.');
            }

            $this->redirect('/email/verify');
        }

        $this->app->session()->flash('success', 'Anmeldung erfolgreich.');
        $this->redirect('/dashboard');
    }

    public function logout(Request $request, array $params = []): void
    {
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new AuthService($this->app);
        $service->logout();

        $this->app->session()->flash('success', 'Abmeldung erfolgreich.');
        $this->redirect('/login');
    }
}
