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

    public function attempt(string $email, string $password): bool
    {
        if ($email === '' || $password === '') {
            return false;
        }

        $user = User::findByEmail($email);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        unset($user['password_hash']);

        $this->app->session()->regenerate();
        $this->app->session()->put((string) $this->app->config('auth.session_key', 'auth_user'), $user);

        return true;
    }

    public function logout(): void
    {
        $this->app->session()->forget((string) $this->app->config('auth.session_key', 'auth_user'));
        $this->app->session()->regenerate();
    }
}
