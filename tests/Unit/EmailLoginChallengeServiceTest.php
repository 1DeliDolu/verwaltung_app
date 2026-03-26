<?php

declare(strict_types=1);

use App\Services\EmailLoginChallengeService;

final class EmailLoginChallengeServiceTest extends TestCase
{
    public function testSecondChallengeInvalidatesPreviousCode(): void
    {
        $capturePath = $this->temporaryPath('email-login-challenge-');

        $this->withEnv([
            'MAIL_CAPTURE_PATH' => $capturePath,
            'AUTH_MFA_EMAIL_CHALLENGE_ROLES' => 'admin',
            'AUTH_MFA_EMAIL_CHALLENGE_EXPIRE_SECONDS' => '600',
        ], function () use ($capturePath): void {
            $this->withDatabaseTransaction(function (\PDO $pdo) use ($capturePath): void {
                $user = $this->userByEmail('admin@verwaltung.local');
                $firstService = new EmailLoginChallengeService(
                    freshTestApp(),
                    new DateTimeImmutable('2030-01-01 10:00:00')
                );
                $secondService = new EmailLoginChallengeService(
                    freshTestApp(),
                    new DateTimeImmutable('2030-01-01 10:02:00')
                );

                $firstPending = $firstService->begin($user, '203.0.113.90', 'EmailLoginChallengeServiceTest/1.0');
                $secondPending = $secondService->begin($user, '203.0.113.91', 'EmailLoginChallengeServiceTest/1.1');

                $messages = $this->capturedMessages($capturePath);
                $this->assertSame(2, count($messages));
                $firstCode = $this->extractLoginCode((string) ($messages[0]['text_body'] ?? ''));
                $secondCode = $this->extractLoginCode((string) ($messages[1]['text_body'] ?? ''));
                $this->assertSame(false, hash_equals($firstCode, $secondCode));

                $rows = $pdo->query(
                    'SELECT requested_ip, consumed_at
                     FROM login_email_challenges
                     WHERE requested_ip IN (\'203.0.113.90\', \'203.0.113.91\')
                     ORDER BY id ASC'
                )->fetchAll();

                $this->assertSame(2, count($rows));
                $this->assertSame('203.0.113.90', $rows[0]['requested_ip'] ?? null);
                $this->assertSame('203.0.113.91', $rows[1]['requested_ip'] ?? null);
                $this->assertSame(false, ($rows[0]['consumed_at'] ?? null) === null);
                $this->assertSame(true, ($rows[1]['consumed_at'] ?? null) === null);

                try {
                    $secondService->verify($firstPending, $firstCode);
                    throw new RuntimeException('Expected the first login challenge to be invalidated.');
                } catch (RuntimeException $exception) {
                    $this->assertSame(EmailLoginChallengeService::INVALID_CODE_MESSAGE, $exception->getMessage());
                }

                $verifiedUser = $secondService->verify($secondPending, $secondCode);
                $this->assertSame('admin@verwaltung.local', $verifiedUser['email'] ?? null);
            });
        });
    }

    public function testExpiredChallengeCodeIsRejected(): void
    {
        $capturePath = $this->temporaryPath('email-login-challenge-');

        $this->withEnv([
            'MAIL_CAPTURE_PATH' => $capturePath,
            'AUTH_MFA_EMAIL_CHALLENGE_ROLES' => 'admin',
            'AUTH_MFA_EMAIL_CHALLENGE_EXPIRE_SECONDS' => '600',
        ], function () use ($capturePath): void {
            $this->withDatabaseTransaction(function (\PDO $pdo) use ($capturePath): void {
                $user = $this->userByEmail('admin@verwaltung.local');
                $issuedService = new EmailLoginChallengeService(
                    freshTestApp(),
                    new DateTimeImmutable('2030-01-01 10:00:00')
                );
                $expiredService = new EmailLoginChallengeService(
                    freshTestApp(),
                    new DateTimeImmutable('2030-01-01 10:11:00')
                );

                $pending = $issuedService->begin($user, '203.0.113.92', 'EmailLoginChallengeServiceTest/2.0');
                $messages = $this->capturedMessages($capturePath);
                $this->assertSame(1, count($messages));
                $code = $this->extractLoginCode((string) ($messages[0]['text_body'] ?? ''));

                try {
                    $expiredService->verify($pending, $code);
                    throw new RuntimeException('Expected the expired login challenge to be rejected.');
                } catch (RuntimeException $exception) {
                    $this->assertSame(EmailLoginChallengeService::INVALID_CODE_MESSAGE, $exception->getMessage());
                }

                $row = $pdo->query(
                    'SELECT consumed_at
                     FROM login_email_challenges
                     ORDER BY id DESC
                     LIMIT 1'
                )->fetch() ?: [];

                $this->assertSame(false, ($row['consumed_at'] ?? null) === null);
            });
        });
    }

    public function testSuccessfulVerificationClearsThrottleState(): void
    {
        $capturePath = $this->temporaryPath('email-login-challenge-');

        $this->withEnv([
            'MAIL_CAPTURE_PATH' => $capturePath,
            'AUTH_MFA_EMAIL_CHALLENGE_ROLES' => 'admin',
            'AUTH_MFA_EMAIL_CHALLENGE_EXPIRE_SECONDS' => '600',
            'AUTH_MFA_EMAIL_CHALLENGE_MAX_ATTEMPTS' => '3',
            'AUTH_MFA_EMAIL_CHALLENGE_DECAY_SECONDS' => '900',
        ], function () use ($capturePath): void {
            $this->withDatabaseTransaction(function (\PDO $pdo) use ($capturePath): void {
                $user = $this->userByEmail('admin@verwaltung.local');
                $service = new EmailLoginChallengeService(
                    freshTestApp(),
                    new DateTimeImmutable('2030-01-01 10:00:00')
                );

                $pending = $service->begin($user, '203.0.113.93', 'EmailLoginChallengeServiceTest/3.0');
                $messages = $this->capturedMessages($capturePath);
                $this->assertSame(1, count($messages));
                $code = $this->extractLoginCode((string) ($messages[0]['text_body'] ?? ''));
                $wrongCode = $code === '999999' ? '000000' : '999999';

                try {
                    $service->verify($pending, $wrongCode);
                    throw new RuntimeException('Expected the wrong login challenge code to be rejected.');
                } catch (RuntimeException $exception) {
                    $this->assertSame(EmailLoginChallengeService::INVALID_CODE_MESSAGE, $exception->getMessage());
                }

                $beforeCount = $pdo->query(
                    'SELECT COUNT(*)
                     FROM login_challenge_attempt_limits
                     WHERE challenge_id = ' . (int) ($pending['challenge_id'] ?? 0)
                )->fetchColumn();
                $this->assertSame(1, (int) $beforeCount);

                $verifiedUser = $service->verify($pending, $code);
                $this->assertSame('admin@verwaltung.local', $verifiedUser['email'] ?? null);

                $afterCount = $pdo->query(
                    'SELECT COUNT(*)
                     FROM login_challenge_attempt_limits
                     WHERE challenge_id = ' . (int) ($pending['challenge_id'] ?? 0)
                )->fetchColumn();
                $this->assertSame(0, (int) $afterCount);
            });
        });
    }

    private function extractLoginCode(string $body): string
    {
        if (preg_match('/\b(\d{6})\b/', $body, $matches) !== 1) {
            throw new RuntimeException('Login code could not be extracted from the captured mail.');
        }

        return $matches[1];
    }
}
