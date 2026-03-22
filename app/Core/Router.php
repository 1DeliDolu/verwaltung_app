<?php

declare(strict_types=1);

namespace App\Core;

use Closure;

final class Router
{
    private array $routes = [];

    public function get(string $path, Closure|array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, Closure|array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function add(string $method, string $path, Closure|array $handler): void
    {
        $normalizedPath = rtrim($path, '/') ?: '/';
        $this->routes[strtoupper($method)][$normalizedPath] = $handler;
    }

    public function dispatch(Request $request, App $app): void
    {
        $method = $request->method();
        $path = $request->path();
        $handler = $this->routes[$method][$path] ?? null;

        if ($handler === null) {
            $app->response()->render('errors/404', ['app' => $app], 'app', 404);
            return;
        }

        if ($handler instanceof Closure) {
            $handler($app, $request);
            return;
        }

        [$controllerClass, $controllerMethod] = $handler;
        $controller = new $controllerClass($app);
        $controller->{$controllerMethod}($request);
    }
}
