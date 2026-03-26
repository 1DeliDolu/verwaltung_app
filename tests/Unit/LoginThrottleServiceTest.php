<?php

declare(strict_types=1);

use App\Services\LoginThrottleService;

final class LoginThrottleServiceTest extends TestCase
{
    public function testLocksAfterConfiguredNumberOfFailures(): void
    {
        $this->withEnv([
            'AUTH_LOGIN_MAX_ATTEMPTS' => '3',
            'AUTH_LOGIN_DECAY_SECONDS' => '900',
        ], function (): void {
            $this->withDatabaseTransaction(function (): void {
                $now = new DateTimeImmutable('2030-01-01 10:00:00');
                $service = new LoginThrottleService(freshTestApp(), $now);

                $service->recordFailure('admin@verwaltung.local', '203.0.113.30');
                $service->recordFailure('admin@verwaltung.local', '203.0.113.30');
                $result = $service->recordFailure('admin@verwaltung.local', '203.0.113.30');

                $this->assertSame(true, $result['locked'] ?? false);

                try {
                    $service->ensureAllowed('admin@verwaltung.local', '203.0.113.30');
                    throw new RuntimeException('Expected the throttle service to block further attempts.');
                } catch (RuntimeException $exception) {
                    $this->assertStringContains('Zu viele Anmeldeversuche.', $exception->getMessage());
                }
            });
        });
    }

    public function testExpiredLockoutIsClearedAfterDecayWindow(): void
    {
        $this->withEnv([
            'AUTH_LOGIN_MAX_ATTEMPTS' => '3',
            'AUTH_LOGIN_DECAY_SECONDS' => '900',
        ], function (): void {
            $this->withDatabaseTransaction(function (): void {
                $ipAddress = '203.0.113.31';
                $lockedAt = new DateTimeImmutable('2030-01-01 10:00:00');
                $service = new LoginThrottleService(freshTestApp(), $lockedAt);

                $service->recordFailure('admin@verwaltung.local', $ipAddress);
                $service->recordFailure('admin@verwaltung.local', $ipAddress);
                $service->recordFailure('admin@verwaltung.local', $ipAddress);

                $expiredService = new LoginThrottleService(
                    freshTestApp(),
                    new DateTimeImmutable('2030-01-01 10:16:00')
                );

                $expiredService->ensureAllowed('admin@verwaltung.local', $ipAddress);
                $result = $expiredService->recordFailure('admin@verwaltung.local', $ipAddress);

                $this->assertSame(false, $result['locked'] ?? true);
                $this->assertSame(1, $result['failed_attempts'] ?? 0);
            });
        });
    }
}
