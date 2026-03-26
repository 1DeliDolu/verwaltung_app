<?php

declare(strict_types=1);

use App\Services\PasswordResetRequestThrottleService;

final class PasswordResetRequestThrottleServiceTest extends TestCase
{
    public function testLocksAfterConfiguredNumberOfRequests(): void
    {
        $this->withEnv([
            'AUTH_PASSWORD_RESET_REQUEST_MAX_ATTEMPTS' => '2',
            'AUTH_PASSWORD_RESET_REQUEST_DECAY_SECONDS' => '900',
        ], function (): void {
            $this->withDatabaseTransaction(function (): void {
                $now = new DateTimeImmutable('2030-01-01 10:00:00');
                $service = new PasswordResetRequestThrottleService(freshTestApp(), $now);

                $service->recordRequest('admin@verwaltung.local', '203.0.113.70');
                $result = $service->recordRequest('admin@verwaltung.local', '203.0.113.70');

                $this->assertSame(true, $result['locked'] ?? false);

                try {
                    $service->ensureAllowed('admin@verwaltung.local', '203.0.113.70');
                    throw new RuntimeException('Expected the forgot-password throttle to block further requests.');
                } catch (RuntimeException $exception) {
                    $this->assertStringContains('Zu viele Passwort-Reset-Anfragen.', $exception->getMessage());
                }
            });
        });
    }

    public function testExpiredForgotPasswordLockClearsAfterDecayWindow(): void
    {
        $this->withEnv([
            'AUTH_PASSWORD_RESET_REQUEST_MAX_ATTEMPTS' => '2',
            'AUTH_PASSWORD_RESET_REQUEST_DECAY_SECONDS' => '900',
        ], function (): void {
            $this->withDatabaseTransaction(function (): void {
                $ipAddress = '203.0.113.71';
                $lockedAt = new DateTimeImmutable('2030-01-01 10:00:00');
                $service = new PasswordResetRequestThrottleService(freshTestApp(), $lockedAt);

                $service->recordRequest('admin@verwaltung.local', $ipAddress);
                $service->recordRequest('admin@verwaltung.local', $ipAddress);

                $expiredService = new PasswordResetRequestThrottleService(
                    freshTestApp(),
                    new DateTimeImmutable('2030-01-01 10:16:00')
                );

                $expiredService->ensureAllowed('admin@verwaltung.local', $ipAddress);
                $result = $expiredService->recordRequest('admin@verwaltung.local', $ipAddress);

                $this->assertSame(false, $result['locked'] ?? true);
                $this->assertSame(1, $result['request_attempts'] ?? 0);
            });
        });
    }
}
