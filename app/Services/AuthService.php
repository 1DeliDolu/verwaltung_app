<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Models\User;

final class AuthService
{
    public function __construct(private readonly App $app)
    {
    }

    public function validateCredentials(string $email, string $password): ?array
    {
        if ($email === '' || $password === '') {
            return null;
        }

        $user = User::findByEmail($email);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        unset($user['password_hash']);

        return $user;
    }

    public function loginUser(array $user): void
    {
        unset($user['password_hash']);

        $this->app->session()->regenerate();
        $this->app->session()->put((string) $this->app->config('auth.session_key', 'auth_user'), $user);
    }

    public function attempt(string $email, string $password): bool
    {
        $user = $this->validateCredentials($email, $password);

        if ($user === null) {
            return false;
        }

        $this->loginUser($user);

        return true;
    }

    public function requiresPasswordChange(array $user): bool
    {
        return ($user['password_change_required_at'] ?? null) !== null;
    }

    public function changePassword(array $user, string $currentPassword, string $newPassword, string $newPasswordConfirmation): void
    {
        if ($currentPassword === '' || $newPassword === '' || $newPasswordConfirmation === '') {
            throw new \RuntimeException('All password fields are required.');
        }

        if ($newPassword !== $newPasswordConfirmation) {
            throw new \RuntimeException('Password confirmation does not match.');
        }

        $storedUser = User::findByEmail((string) ($user['email'] ?? ''));

        if ($storedUser === null || !password_verify($currentPassword, (string) ($storedUser['password_hash'] ?? ''))) {
            throw new \RuntimeException('Current password is invalid.');
        }

        $this->assertPasswordStrength($newPassword, (string) ($user['email'] ?? ''), (string) ($user['name'] ?? ''));

        if (password_verify($newPassword, (string) ($storedUser['password_hash'] ?? ''))) {
            throw new \RuntimeException('New password must differ from the current password.');
        }

        User::updatePassword((int) $user['id'], password_hash($newPassword, PASSWORD_DEFAULT));
        $refreshedUser = User::findById((int) $user['id']);

        if ($refreshedUser === null) {
            throw new \RuntimeException('Updated user could not be loaded.');
        }

        $this->app->session()->put((string) $this->app->config('auth.session_key', 'auth_user'), $refreshedUser);
    }

    public function assertPasswordStrength(string $password, string $email = '', string $name = ''): void
    {
        if (strlen($password) < 12) {
            throw new \RuntimeException('Password must contain at least 12 characters.');
        }

        if (!preg_match('/[A-Z]/', $password)
            || !preg_match('/[a-z]/', $password)
            || !preg_match('/\d/', $password)
            || !preg_match('/[^A-Za-z0-9]/', $password)) {
            throw new \RuntimeException('Password complexity requirements are not met.');
        }

        $forbiddenFragments = array_filter([
            strtolower(trim($email)),
            strtolower(trim((string) strtok($email, '@'))),
            strtolower(trim($name)),
        ]);
        $normalizedPassword = strtolower($password);

        foreach ($forbiddenFragments as $fragment) {
            if ($fragment !== '' && str_contains($normalizedPassword, $fragment)) {
                throw new \RuntimeException('Password must not contain personal identifiers.');
            }
        }
    }

    public function logout(): void
    {
        $this->app->session()->forget((string) $this->app->config('auth.session_key', 'auth_user'));
        $this->app->session()->forget((string) $this->app->config('auth.pending_mfa_key', 'auth_pending_mfa'));
        $this->app->session()->regenerate();
    }
}
