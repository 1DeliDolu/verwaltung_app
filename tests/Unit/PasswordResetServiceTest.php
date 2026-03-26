<?php

declare(strict_types=1);

use App\Services\PasswordResetService;

final class PasswordResetServiceTest extends TestCase
{
    public function testSecondResetRequestInvalidatesPreviousToken(): void
    {
        $capturePath = $this->temporaryPath('forgot-unit-');

        $this->withEnv([
            'MAIL_CAPTURE_PATH' => $capturePath,
            'AUTH_PASSWORD_RESET_EXPIRE_SECONDS' => '3600',
        ], function () use ($capturePath): void {
            $this->withDatabaseTransaction(function (\PDO $pdo) use ($capturePath): void {
                $firstService = new PasswordResetService(
                    freshTestApp(),
                    new DateTimeImmutable('2030-01-01 10:00:00')
                );
                $secondService = new PasswordResetService(
                    freshTestApp(),
                    new DateTimeImmutable('2030-01-01 10:05:00')
                );

                $firstService->requestLink('admin@verwaltung.local', '203.0.113.60', 'PasswordResetServiceTest/1.0');
                $secondService->requestLink('admin@verwaltung.local', '203.0.113.61', 'PasswordResetServiceTest/1.1');

                $messages = $this->capturedMessages($capturePath);
                $this->assertSame(2, count($messages));

                $firstToken = $this->extractResetToken((string) ($messages[0]['text_body'] ?? ''));
                $secondToken = $this->extractResetToken((string) ($messages[1]['text_body'] ?? ''));
                $this->assertSame(false, hash_equals($firstToken, $secondToken));

                $rows = $pdo->query(
                    'SELECT token_hash, requested_ip, used_at
                     FROM password_reset_tokens
                     ORDER BY id ASC'
                )->fetchAll();

                $this->assertSame(2, count($rows));
                $this->assertSame(hash('sha256', $firstToken), $rows[0]['token_hash'] ?? null);
                $this->assertSame(hash('sha256', $secondToken), $rows[1]['token_hash'] ?? null);
                $this->assertSame('203.0.113.60', $rows[0]['requested_ip'] ?? null);
                $this->assertSame('203.0.113.61', $rows[1]['requested_ip'] ?? null);
                $this->assertSame(false, ($rows[0]['used_at'] ?? null) === null);
                $this->assertSame(true, ($rows[1]['used_at'] ?? null) === null);

                try {
                    $secondService->previewToken($firstToken);
                    throw new RuntimeException('Expected the first token to be invalidated.');
                } catch (RuntimeException $exception) {
                    $this->assertSame(PasswordResetService::INVALID_TOKEN_MESSAGE, $exception->getMessage());
                }

                $preview = $secondService->previewToken($secondToken);
                $this->assertSame('admin@verwaltung.local', $preview['email'] ?? null);
            });
        });
    }

    public function testExpiredTokenIsRejected(): void
    {
        $capturePath = $this->temporaryPath('forgot-unit-');

        $this->withEnv([
            'MAIL_CAPTURE_PATH' => $capturePath,
            'AUTH_PASSWORD_RESET_EXPIRE_SECONDS' => '900',
        ], function () use ($capturePath): void {
            $this->withDatabaseTransaction(function (\PDO $pdo) use ($capturePath): void {
                $issuedService = new PasswordResetService(
                    freshTestApp(),
                    new DateTimeImmutable('2030-01-01 10:00:00')
                );
                $expiredService = new PasswordResetService(
                    freshTestApp(),
                    new DateTimeImmutable('2030-01-01 10:16:00')
                );

                $issuedService->requestLink('admin@verwaltung.local', '203.0.113.62', 'PasswordResetServiceTest/2.0');

                $messages = $this->capturedMessages($capturePath);
                $this->assertSame(1, count($messages));
                $token = $this->extractResetToken((string) ($messages[0]['text_body'] ?? ''));

                try {
                    $expiredService->resetPassword($token, 'N3ues!Passwort123', 'N3ues!Passwort123');
                    throw new RuntimeException('Expected the expired reset token to be rejected.');
                } catch (RuntimeException $exception) {
                    $this->assertSame(PasswordResetService::INVALID_TOKEN_MESSAGE, $exception->getMessage());
                }

                $row = $pdo->query(
                    'SELECT used_at
                     FROM password_reset_tokens
                     ORDER BY id DESC
                     LIMIT 1'
                )->fetch() ?: [];

                $this->assertSame(false, ($row['used_at'] ?? null) === null);
            });
        });
    }

    private function extractResetToken(string $body): string
    {
        if (preg_match('#/password/reset/([a-f0-9]+)#', $body, $matches) !== 1) {
            throw new RuntimeException('Reset token could not be extracted from the captured mail.');
        }

        return $matches[1];
    }
}
