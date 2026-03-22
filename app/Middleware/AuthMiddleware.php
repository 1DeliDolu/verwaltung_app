<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\App;

final class AuthMiddleware
{
    public static function handle(App $app): void
    {
        if ($app->session()->get((string) $app->config('auth.session_key', 'auth_user')) !== null) {
            return;
        }

        $app->session()->flash('error', 'Bu sayfaya erismek icin giris yapman gerekiyor.');
        $app->response()->redirect('/login');
    }
}
