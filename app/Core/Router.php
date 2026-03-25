<?php

declare(strict_types=1);

namespace App\Core;

use Closure;

final class Router
{
    private array $routes = [];
    private array $groupPrefixes = [];

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
        $normalizedPath = $this->normalizePath($this->applyGroupPrefix($path));
        $this->routes[strtoupper($method)][$normalizedPath] = $handler;
    }

    public function group(string $prefix, Closure $routes): void
    {
        $this->groupPrefixes[] = $this->normalizeGroupPrefix($prefix);

        try {
            $routes($this);
        } finally {
            array_pop($this->groupPrefixes);
        }
    }

    public function dispatch(Request $request, App $app): void
    {
        $method = $request->method();
        $path = $request->path();
        $matchedRoute = $this->match($method, $path);

        if ($matchedRoute === null) {
            $app->response()->render('errors/404', ['app' => $app], 'app', 404);
            return;
        }

        $handler = $matchedRoute['handler'];
        $params = $matchedRoute['params'];

        if ($handler instanceof Closure) {
            $handler($app, $request, $params);
            return;
        }

        [$controllerClass, $controllerMethod] = $handler;
        $controller = new $controllerClass($app);
        $controller->{$controllerMethod}($request, $params);
    }

    private function match(string $method, string $path): ?array
    {
        foreach ($this->routes[$method] ?? [] as $routePath => $handler) {
            $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $routePath);
            $pattern = '#^' . $pattern . '$#';

            if (!preg_match($pattern, $path, $matches)) {
                continue;
            }

            $params = [];

            foreach ($matches as $key => $value) {
                if (!is_string($key)) {
                    continue;
                }

                $params[$key] = $value;
            }

            return [
                'handler' => $handler,
                'params' => $params,
            ];
        }

        return null;
    }

    private function applyGroupPrefix(string $path): string
    {
        $prefix = implode('', $this->groupPrefixes);

        if ($prefix === '') {
            return $path;
        }

        if ($path === '/' || $path === '') {
            return $prefix;
        }

        return $prefix . '/' . ltrim($path, '/');
    }

    private function normalizePath(string $path): string
    {
        return rtrim($path, '/') ?: '/';
    }

    private function normalizeGroupPrefix(string $prefix): string
    {
        $normalizedPrefix = trim($prefix, '/');

        return $normalizedPrefix === '' ? '' : '/' . $normalizedPrefix;
    }
}
