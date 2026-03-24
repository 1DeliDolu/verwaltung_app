<?php

declare(strict_types=1);

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
}
