<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\App;

final class VerifiedMiddleware
{
    public static function handle(App $app): void
    {
        $user = $app->session()->get((string) $app->config('auth.session_key', 'auth_user'));

        if (($user['email_verified_at'] ?? null) !== null) {
            return;
        }

        $app->session()->flash('error', 'Bitte bestaetige zuerst deine E-Mail-Adresse.');
        $app->response()->redirect('/email/verify');
    }
}
