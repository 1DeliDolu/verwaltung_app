<?php

declare(strict_types=1);

use App\Core\RedirectException;
use App\Core\Database;
use App\Models\User;

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

    protected function dispatchApp(string $method, string $uri, array $session = [], array $post = [], array $server = []): array
    {
        $queryString = parse_url($uri, PHP_URL_QUERY);
        $query = [];

        if (is_string($queryString) && $queryString !== '') {
            parse_str($queryString, $query);
        }

        $_GET = $query;
        $_POST = $post;
        $_FILES = [];
        $_SERVER = array_merge($_SERVER, [
            'REQUEST_METHOD' => strtoupper($method),
            'REQUEST_URI' => $uri,
        ], $server);
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

    protected function withEnv(array $variables, callable $callback): void
    {
        $snapshot = [];

        foreach ($variables as $key => $value) {
            $snapshot[$key] = getenv($key);

            if ($value === null) {
                unset($_ENV[$key], $_SERVER[$key]);
                putenv($key);
                continue;
            }

            $_ENV[$key] = (string) $value;
            $_SERVER[$key] = (string) $value;
            putenv($key . '=' . $value);
        }

        try {
            $callback();
        } finally {
            foreach ($variables as $key => $value) {
                $previous = $snapshot[$key];

                if ($previous === false) {
                    unset($_ENV[$key], $_SERVER[$key]);
                    putenv($key);
                    continue;
                }

                $_ENV[$key] = (string) $previous;
                $_SERVER[$key] = (string) $previous;
                putenv($key . '=' . $previous);
            }
        }
    }

    protected function temporaryPath(string $prefix = 'verwaltung-test-'): string
    {
        $path = tempnam(sys_get_temp_dir(), $prefix);

        if ($path === false) {
            throw new RuntimeException('Temporary path could not be allocated.');
        }

        if (is_file($path)) {
            unlink($path);
        }

        return $path;
    }

    protected function capturedMessages(string $capturePath): array
    {
        if (!is_file($capturePath)) {
            return [];
        }

        $lines = file($capturePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            throw new RuntimeException('Captured messages could not be read.');
        }

        return array_map(
            static fn (string $line): array => json_decode($line, true, 512, JSON_THROW_ON_ERROR),
            $lines
        );
    }

    protected function pdo(): \PDO
    {
        testApp();

        return Database::instance()->pdo();
    }

    protected function withDatabaseTransaction(callable $callback): void
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $callback($pdo);
        } finally {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        }
    }

    protected function userByEmail(string $email): array
    {
        $user = User::findByEmail($email);

        if ($user === null) {
            throw new RuntimeException('Required test user not found: ' . $email);
        }

        unset($user['password_hash']);

        return $user;
    }
}
