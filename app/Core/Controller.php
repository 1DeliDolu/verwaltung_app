<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    protected function render(string $view, array $data = [], string $layout = 'app'): void
    {
        $this->app->response()->render($view, $data, $layout);
    }

    protected function redirect(string $path): void
    {
        $this->app->response()->redirect($path);
    }
}
