<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\App;

final class CsrfMiddleware
{
    public static function token(App $app): string
    {
        $key = (string) $app->config('auth.csrf_key', '_csrf_token');
        $token = $app->session()->get($key);

        if (is_string($token) && $token !== '') {
            return $token;
        }

        $token = bin2hex(random_bytes(32));
        $app->session()->put($key, $token);

        return $token;
    }

    public static function validate(App $app, string $token): void
    {
        $key = (string) $app->config('auth.csrf_key', '_csrf_token');
        $sessionToken = (string) $app->session()->get($key, '');

        if ($sessionToken !== '' && hash_equals($sessionToken, $token)) {
            return;
        }

        http_response_code(419);
        echo 'Ungueltiges CSRF-Token.';
        exit;
    }
}
