<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Services\EmailVerificationService;
use App\Services\InternalMailService;

final class VerificationController extends Controller
{
    public function notice(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $user = (new InternalMailService($this->app))->currentUser();

        if (($user['email_verified_at'] ?? null) !== null) {
            $this->redirect('/dashboard');
        }

        $this->render('auth/verify-email', [
            'app' => $this->app,
            'user' => $user,
            'csrfToken' => \App\Middleware\CsrfMiddleware::token($this->app),
            'success' => $this->app->session()->consumeFlash('success'),
            'error' => $this->app->session()->consumeFlash('error'),
        ]);
    }

    public function resend(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        \App\Middleware\CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $user = (new InternalMailService($this->app))->currentUser();

        try {
            (new EmailVerificationService($this->app))->sendVerificationMail($user);
            $this->app->session()->flash('success', 'Verifizierungs-E-Mail wurde erneut gesendet.');
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', 'Verifizierungs-E-Mail konnte nicht gesendet werden.');
        }

        $this->redirect('/email/verify');
    }

    public function verify(Request $request, array $params = []): void
    {
        $userId = (int) ($params['id'] ?? 0);
        $token = (string) ($params['token'] ?? '');

        try {
            (new EmailVerificationService($this->app))->verify($userId, $token);

            $authUser = $this->app->session()->get((string) $this->app->config('auth.session_key', 'auth_user'));
            if (($authUser['id'] ?? null) === $userId) {
                $authUser['email_verified_at'] = date('Y-m-d H:i:s');
                $this->app->session()->put((string) $this->app->config('auth.session_key', 'auth_user'), $authUser);
            }

            $this->app->session()->flash('success', 'E-Mail-Adresse erfolgreich bestaetigt.');
            $this->redirect('/dashboard');
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', 'Der Verifizierungslink ist ungueltig oder abgelaufen.');
            $this->redirect('/email/verify');
        }
    }
}
