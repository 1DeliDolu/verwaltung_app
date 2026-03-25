<?php

declare(strict_types=1);

use App\Core\RedirectException;

abstract class TestCase
{
    protected function assertTrue(bool $condition, string $message = 'Expected condition to be true.'): void
    {
        if (!$condition) {
            throw new RuntimeException($message);
        }
    }

    protected function assertSame(mixed $expected, mixed $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            throw new RuntimeException($message !== '' ? $message : 'Failed asserting values are identical.');
        }
    }

    protected function assertStringContains(string $needle, string $haystack, string $message = ''): void
    {
        if (!str_contains($haystack, $needle)) {
            throw new RuntimeException($message !== '' ? $message : 'Failed asserting that the string contains the expected fragment.');
        }
    }

    protected function expectException(callable $callback, string $expectedClass = RuntimeException::class): void
    {
        try {
            $callback();
        } catch (Throwable $throwable) {
            if ($throwable instanceof $expectedClass) {
                return;
            }

            throw new RuntimeException(
                sprintf('Expected exception %s, got %s.', $expectedClass, $throwable::class),
                0,
                $throwable
            );
        }

        throw new RuntimeException(sprintf('Expected exception %s was not thrown.', $expectedClass));
    }

    protected function dispatchApp(string $method, string $uri, array $session = [], array $post = []): array
    {
        $_GET = [];
        $_POST = $post;
        $_FILES = [];
        $_SERVER['REQUEST_METHOD'] = strtoupper($method);
        $_SERVER['REQUEST_URI'] = $uri;
        $_SESSION = [];

        http_response_code(200);

        $app = freshTestApp();
        $_SESSION = $session;
        require BASE_PATH . '/routes/web.php';

        $redirectTo = null;
        $content = '';

        ob_start();

        try {
            $app->run();
        } catch (RedirectException $exception) {
            $redirectTo = $exception->path();
        } finally {
            $content = (string) ob_get_clean();
        }

        return [
            'status' => http_response_code(),
            'redirect_to' => $redirectTo,
            'content' => $content,
            'session' => $_SESSION,
        ];
    }
}
