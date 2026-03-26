<?php

declare(strict_types=1);

use App\Models\LoginEmailChallenge;
use App\Services\LoginChallengeThrottleService;

final class LoginChallengeThrottleServiceTest extends TestCase
{
    public function testLocksAfterConfiguredNumberOfFailures(): void
    {
        $this->withEnv([
            'AUTH_MFA_EMAIL_CHALLENGE_MAX_ATTEMPTS' => '3',
            'AUTH_MFA_EMAIL_CHALLENGE_DECAY_SECONDS' => '900',
        ], function (): void {
            $this->withDatabaseTransaction(function (): void {
                $challengeId = $this->createChallenge('2030-01-01 10:10:00');
                $service = new LoginChallengeThrottleService(
                    freshTestApp(),
                    new DateTimeImmutable('2030-01-01 10:00:00')
                );

                $service->recordFailure($challengeId, '203.0.113.40');
                $service->recordFailure($challengeId, '203.0.113.40');
                $result = $service->recordFailure($challengeId, '203.0.113.40');

                $this->assertSame(true, $result['locked'] ?? false);

                try {
                    $service->ensureAllowed($challengeId, '203.0.113.40');
                    throw new RuntimeException('Expected the login challenge throttle to block further attempts.');
                } catch (RuntimeException $exception) {
                    $this->assertStringContains('Zu viele falsche Anmeldecodes.', $exception->getMessage());
                }
            });
        });
    }

    public function testExpiredLockoutIsClearedAfterDecayWindow(): void
    {
        $this->withEnv([
            'AUTH_MFA_EMAIL_CHALLENGE_MAX_ATTEMPTS' => '3',
            'AUTH_MFA_EMAIL_CHALLENGE_DECAY_SECONDS' => '900',
        ], function (): void {
            $this->withDatabaseTransaction(function (): void {
                $challengeId = $this->createChallenge('2030-01-01 10:10:00');
                $ipAddress = '203.0.113.41';
                $service = new LoginChallengeThrottleService(
                    freshTestApp(),
                    new DateTimeImmutable('2030-01-01 10:00:00')
                );

                $service->recordFailure($challengeId, $ipAddress);
                $service->recordFailure($challengeId, $ipAddress);
                $service->recordFailure($challengeId, $ipAddress);

                $expiredService = new LoginChallengeThrottleService(
                    freshTestApp(),
                    new DateTimeImmutable('2030-01-01 10:16:00')
                );

                $expiredService->ensureAllowed($challengeId, $ipAddress);
                $result = $expiredService->recordFailure($challengeId, $ipAddress);

                $this->assertSame(false, $result['locked'] ?? true);
                $this->assertSame(1, $result['failed_attempts'] ?? 0);
            });
        });
    }

    private function createChallenge(string $expiresAt): int
    {
        $user = $this->userByEmail('admin@verwaltung.local');

        return LoginEmailChallenge::create([
            'user_id' => (int) ($user['id'] ?? 0),
            'code_hash' => hash('sha256', '123456'),
            'requested_ip' => '203.0.113.40',
            'requested_user_agent' => 'LoginChallengeThrottleServiceTest/1.0',
            'expires_at' => $expiresAt,
        ]);
    }
}
