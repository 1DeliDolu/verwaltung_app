<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function __construct(
        private readonly array $get,
        private readonly array $post,
        private readonly array $files,
        private readonly array $server
    ) {
    }

    public static function capture(): self
    {
        return new self($_GET, $_POST, $_FILES, $_SERVER);
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);

        if ($path === false || $path === null) {
            return '/';
        }

        $normalized = rtrim($path, '/');

        return $normalized === '' ? '/' : $normalized;
    }

    public function ip(): ?string
    {
        $candidate = $this->server['REMOTE_ADDR'] ?? null;

        return is_string($candidate) && trim($candidate) !== '' ? trim($candidate) : null;
    }

    public function userAgent(): ?string
    {
        $candidate = $this->server['HTTP_USER_AGENT'] ?? null;

        return is_string($candidate) && trim($candidate) !== '' ? trim($candidate) : null;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function only(array $keys): array
    {
        $data = [];

        foreach ($keys as $key) {
            $data[$key] = $this->input($key);
        }

        return $data;
    }

    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;

        return is_array($file) ? $file : null;
    }
}
