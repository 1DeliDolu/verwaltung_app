<?php

declare(strict_types=1);

namespace App\Core;

final class App
{
    private array $config;
    private Session $session;
    private Request $request;
    private Response $response;
    private Router $router;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->session = new Session();
        $this->request = Request::capture();
        $this->response = new Response();
        $this->router = new Router();
    }

    public function config(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $this->config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public function session(): Session
    {
        return $this->session;
    }

    public function request(): Request
    {
        return $this->request;
    }

    public function response(): Response
    {
        return $this->response;
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function run(): void
    {
        $authUser = $this->session->get((string) $this->config('auth.session_key', 'auth_user'));
        $currentPath = $this->request->path();

        if (is_array($authUser)
            && ($authUser['password_change_required_at'] ?? null) !== null
            && !in_array($currentPath, ['/password/change', '/logout'], true)) {
            $this->response->redirect('/password/change');
        }

        $this->router->dispatch($this->request, $this);
    }
}
