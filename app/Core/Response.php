<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public function render(string $view, array $data = [], string $layout = 'app', int $status = 200): void
    {
        http_response_code($status);
        echo View::render($view, $data, $layout);
    }

    public function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}
