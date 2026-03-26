<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\CsrfMiddleware;
use App\Services\AuthService;
use App\Services\EmailLoginChallengeService;
use App\Services\LoginThrottleService;
use App\Services\PasswordResetService;

final class AuthController extends Controller
{
    public function showLogin(Request $request, array $params = []): void
    {
        if ($this->pendingChallenge() !== null) {
            $this->redirect('/login/challenge');
        }

        $authUser = $this->app->session()->get($this->app->config('auth.session_key'));

        if ($authUser !== null) {
            $service = new AuthService($this->app);
            $this->redirect($service->requiresPasswordChange((array) $authUser) ? '/password/change' : '/dashboard');
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

    public function showLoginChallenge(Request $request, array $params = []): void
    {
        $this->redirectAuthenticatedUser();
        $pending = $this->pendingChallenge();

        if ($pending === null) {
            $this->redirect('/login');
        }

        $this->render('auth/login-challenge', [
            'app' => $this->app,
            'csrfToken' => CsrfMiddleware::token($this->app),
            'email' => (string) ($pending['email'] ?? ''),
            'error' => $this->app->session()->consumeFlash('error'),
            'success' => $this->app->session()->consumeFlash('success'),
        ]);
    }

    public function showForgotPassword(Request $request, array $params = []): void
    {
        $this->redirectAuthenticatedUser();

        $this->render('auth/forgot-password', [
            'app' => $this->app,
            'csrfToken' => CsrfMiddleware::token($this->app),
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
        $throttle = new LoginThrottleService($this->app);

        try {
            $throttle->ensureAllowed($credentials['email'], $request->ip());
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', $exception->getMessage());
            $this->app->session()->flash('old_email', $credentials['email']);
            $this->redirect('/login');
        }

        $user = $service->validateCredentials($credentials['email'], $credentials['password']);

        if ($user === null) {
            $failure = $throttle->recordFailure($credentials['email'], $request->ip());
            $message = ($failure['locked'] ?? false) === true
                ? $throttle->lockoutMessage((int) ($failure['available_in_seconds'] ?? 0))
                : 'E-Mail oder Passwort ist ungueltig.';

            $this->app->session()->flash('error', $message);
            $this->app->session()->flash('old_email', $credentials['email']);
            $this->redirect('/login');
        }

        $throttle->clear($credentials['email'], $request->ip());
        $challengeService = new EmailLoginChallengeService($this->app);

        if ($challengeService->requiresChallenge($user)) {
            $this->app->session()->forget((string) $this->app->config('auth.pending_mfa_key', 'auth_pending_mfa'));

            try {
                $pending = $challengeService->begin($user, $request->ip(), $request->userAgent());
            } catch (\RuntimeException $exception) {
                $this->app->session()->flash('error', 'Anmeldung konnte nicht abgeschlossen werden. Bitte versuche es erneut.');
                $this->redirect('/login');
            }

            $this->app->session()->put((string) $this->app->config('auth.pending_mfa_key', 'auth_pending_mfa'), $pending);
            $this->app->session()->flash('success', 'Anmeldecode wurde per E-Mail gesendet.');
            $this->redirect('/login/challenge');
        }

        $service->loginUser($user);
        $this->completeLogin($service, $user);
    }

    public function verifyLoginChallenge(Request $request, array $params = []): void
    {
        $this->redirectAuthenticatedUser();
        $pending = $this->pendingChallenge();

        if ($pending === null) {
            $this->redirect('/login');
        }

        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        try {
            $user = (new EmailLoginChallengeService($this->app))->verify(
                $pending,
                (string) $request->input('code', '')
            );
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', $exception->getMessage());
            $this->redirect('/login/challenge');
        }

        $this->app->session()->forget((string) $this->app->config('auth.pending_mfa_key', 'auth_pending_mfa'));
        $service = new AuthService($this->app);
        $service->loginUser($user);
        $this->completeLogin($service, $user);
    }

    public function requestPasswordReset(Request $request, array $params = []): void
    {
        $this->redirectAuthenticatedUser();
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $email = trim((string) $request->input('email', ''));
        $service = new PasswordResetService($this->app);

        try {
            $service->requestLink($email, $request->ip(), $request->userAgent());
        } catch (\RuntimeException $exception) {
            if ($exception->getMessage() === PasswordResetService::INVALID_REQUEST_MESSAGE) {
                $this->app->session()->flash('error', $exception->getMessage());
                $this->app->session()->flash('old_email', $email);
                $this->redirect('/password/forgot');
            }
        }

        $this->app->session()->flash(
            'success',
            'Wenn ein Konto mit dieser E-Mail-Adresse existiert, wurde ein Reset-Link gesendet.'
        );
        $this->redirect('/password/forgot');
    }

    public function logout(Request $request, array $params = []): void
    {
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new AuthService($this->app);
        $service->logout();

        $this->app->session()->flash('success', 'Abmeldung erfolgreich.');
        $this->redirect('/login');
    }

    public function showPasswordChange(Request $request, array $params = []): void
    {
        $authUser = $this->app->session()->get((string) $this->app->config('auth.session_key', 'auth_user'));

        if (!is_array($authUser)) {
            $this->redirect('/login');
        }

        $this->render('auth/change-password', [
            'app' => $this->app,
            'user' => $authUser,
            'csrfToken' => CsrfMiddleware::token($this->app),
            'error' => $this->app->session()->consumeFlash('error'),
            'success' => $this->app->session()->consumeFlash('success'),
        ]);
    }

    public function showPasswordReset(Request $request, array $params = []): void
    {
        $this->redirectAuthenticatedUser();

        $token = (string) ($params['token'] ?? '');

        try {
            $context = (new PasswordResetService($this->app))->previewToken($token);
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', PasswordResetService::INVALID_TOKEN_MESSAGE);
            $this->redirect('/password/forgot');
        }

        $this->render('auth/reset-password', [
            'app' => $this->app,
            'csrfToken' => CsrfMiddleware::token($this->app),
            'token' => $token,
            'email' => $context['email'] ?? '',
            'error' => $this->app->session()->consumeFlash('error'),
            'success' => $this->app->session()->consumeFlash('success'),
        ]);
    }

    public function changePassword(Request $request, array $params = []): void
    {
        $authUser = $this->app->session()->get((string) $this->app->config('auth.session_key', 'auth_user'));

        if (!is_array($authUser)) {
            $this->redirect('/login');
        }

        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        try {
            (new AuthService($this->app))->changePassword(
                (array) $authUser,
                (string) $request->input('current_password', ''),
                (string) $request->input('new_password', ''),
                (string) $request->input('new_password_confirmation', '')
            );
            $this->app->session()->flash('success', 'Passwort wurde erfolgreich aktualisiert.');
            $this->redirect('/dashboard');
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', 'Passwort konnte nicht aktualisiert werden. Bitte pruefe die Passwortregeln.');
            $this->redirect('/password/change');
        }
    }

    public function resetPasswordWithToken(Request $request, array $params = []): void
    {
        $this->redirectAuthenticatedUser();
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $token = (string) ($params['token'] ?? '');

        try {
            (new PasswordResetService($this->app))->resetPassword(
                $token,
                (string) $request->input('password', ''),
                (string) $request->input('password_confirmation', '')
            );
        } catch (\RuntimeException $exception) {
            if ($exception->getMessage() === PasswordResetService::INVALID_TOKEN_MESSAGE) {
                $this->app->session()->flash('error', $exception->getMessage());
                $this->redirect('/password/forgot');
            }

            $this->app->session()->flash(
                'error',
                'Passwort konnte nicht zurueckgesetzt werden. Bitte pruefe die Passwortregeln.'
            );
            $this->redirect('/password/reset/' . rawurlencode($token));
        }

        $this->app->session()->flash('success', 'Passwort wurde zurueckgesetzt. Du kannst dich jetzt anmelden.');
        $this->redirect('/login');
    }

    private function redirectAuthenticatedUser(): void
    {
        $authUser = $this->app->session()->get($this->app->config('auth.session_key'));

        if (!is_array($authUser)) {
            return;
        }

        $service = new AuthService($this->app);
        $this->redirect($service->requiresPasswordChange((array) $authUser) ? '/password/change' : '/dashboard');
    }

    private function pendingChallenge(): ?array
    {
        $pending = $this->app->session()->get((string) $this->app->config('auth.pending_mfa_key', 'auth_pending_mfa'));

        return is_array($pending) ? $pending : null;
    }

    private function completeLogin(AuthService $service, array $user): void
    {
        $this->app->session()->flash('success', 'Anmeldung erfolgreich.');
        $this->redirect($service->requiresPasswordChange($user) ? '/password/change' : '/dashboard');
    }
}
