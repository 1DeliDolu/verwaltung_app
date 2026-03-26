<?php

declare(strict_types=1);

use App\Models\User;

final class ForgotPasswordTest extends TestCase
{
    public function testGuestMayRequestPasswordResetAndCaptureMail(): void
    {
        $capturePath = $this->temporaryPath('forgot-mail-');

        $this->withEnv([
            'MAIL_CAPTURE_PATH' => $capturePath,
            'AUTH_PASSWORD_RESET_EXPIRE_SECONDS' => '3600',
        ], function () use ($capturePath): void {
            $this->withDatabaseTransaction(function (\PDO $pdo) use ($capturePath): void {
                $result = $this->dispatchApp(
                    'POST',
                    '/password/forgot',
                    ['_csrf_token' => 'forgot-token'],
                    [
                        '_token' => 'forgot-token',
                        'email' => 'admin@verwaltung.local',
                    ],
                    [
                        'HTTP_HOST' => 'verwaltung.local',
                        'REMOTE_ADDR' => '203.0.113.50',
                        'HTTP_USER_AGENT' => 'ForgotPasswordTest/1.0',
                    ]
                );

                $this->assertSame('/password/forgot', $result['redirect_to']);
                $this->assertSame(
                    'Wenn ein Konto mit dieser E-Mail-Adresse existiert, wurde ein Reset-Link gesendet.',
                    $result['session']['_flash']['success'] ?? null
                );

                $messages = $this->capturedMessages($capturePath);
                $this->assertSame(1, count($messages));
                $this->assertSame(['admin@verwaltung.local'], $messages[0]['to'] ?? []);
                $this->assertSame('Passwort zuruecksetzen', $messages[0]['subject'] ?? null);

                $token = $this->extractResetToken((string) ($messages[0]['text_body'] ?? ''));
                $statement = $pdo->query(
                    'SELECT token_hash, requested_ip, requested_user_agent, used_at
                     FROM password_reset_tokens
                     ORDER BY id ASC'
                );
                $record = $statement->fetch() ?: [];

                $this->assertSame(hash('sha256', $token), $record['token_hash'] ?? null);
                $this->assertSame('203.0.113.50', $record['requested_ip'] ?? null);
                $this->assertSame('ForgotPasswordTest/1.0', $record['requested_user_agent'] ?? null);
                $this->assertSame(null, $record['used_at'] ?? null);
            });
        });
    }

    public function testForgotPasswordRequestStaysGenericForUnknownEmail(): void
    {
        $capturePath = $this->temporaryPath('forgot-mail-');

        $this->withEnv([
            'MAIL_CAPTURE_PATH' => $capturePath,
        ], function () use ($capturePath): void {
            $this->withDatabaseTransaction(function (\PDO $pdo) use ($capturePath): void {
                $result = $this->dispatchApp(
                    'POST',
                    '/password/forgot',
                    ['_csrf_token' => 'forgot-token'],
                    [
                        '_token' => 'forgot-token',
                        'email' => 'unknown@verwaltung.local',
                    ],
                    [
                        'HTTP_HOST' => 'verwaltung.local',
                        'REMOTE_ADDR' => '203.0.113.51',
                    ]
                );

                $this->assertSame('/password/forgot', $result['redirect_to']);
                $this->assertSame(
                    'Wenn ein Konto mit dieser E-Mail-Adresse existiert, wurde ein Reset-Link gesendet.',
                    $result['session']['_flash']['success'] ?? null
                );
                $this->assertSame([], $this->capturedMessages($capturePath));

                $count = $pdo->query('SELECT COUNT(*) FROM password_reset_tokens')->fetchColumn();
                $this->assertSame(0, (int) $count);
            });
        });
    }

    public function testGuestMayResetPasswordWithValidTokenAndCannotReuseIt(): void
    {
        $capturePath = $this->temporaryPath('forgot-mail-');

        $this->withEnv([
            'MAIL_CAPTURE_PATH' => $capturePath,
            'AUTH_PASSWORD_RESET_EXPIRE_SECONDS' => '3600',
        ], function () use ($capturePath): void {
            $this->withDatabaseTransaction(function () use ($capturePath): void {
                $request = $this->dispatchApp(
                    'POST',
                    '/password/forgot',
                    ['_csrf_token' => 'forgot-token'],
                    [
                        '_token' => 'forgot-token',
                        'email' => 'admin@verwaltung.local',
                    ],
                    [
                        'HTTP_HOST' => 'verwaltung.local',
                        'REMOTE_ADDR' => '203.0.113.52',
                    ]
                );

                $this->assertSame('/password/forgot', $request['redirect_to']);

                $messages = $this->capturedMessages($capturePath);
                $this->assertSame(1, count($messages));
                $token = $this->extractResetToken((string) ($messages[0]['text_body'] ?? ''));

                $form = $this->dispatchApp(
                    'GET',
                    '/password/reset/' . rawurlencode($token),
                    [],
                    [],
                    ['HTTP_HOST' => 'verwaltung.local']
                );

                $this->assertSame(200, $form['status']);
                $this->assertStringContains('Neues Passwort setzen', $form['content']);

                $reset = $this->dispatchApp(
                    'POST',
                    '/password/reset/' . rawurlencode($token),
                    ['_csrf_token' => 'reset-token'],
                    [
                        '_token' => 'reset-token',
                        'password' => 'N3ues!Passwort123',
                        'password_confirmation' => 'N3ues!Passwort123',
                    ],
                    ['HTTP_HOST' => 'verwaltung.local']
                );

                $this->assertSame('/login', $reset['redirect_to']);
                $this->assertSame(
                    'Passwort wurde zurueckgesetzt. Du kannst dich jetzt anmelden.',
                    $reset['session']['_flash']['success'] ?? null
                );

                $user = User::findByEmail('admin@verwaltung.local') ?? [];
                $this->assertSame(true, password_verify('N3ues!Passwort123', (string) ($user['password_hash'] ?? '')));
                $this->assertSame(null, $user['password_change_required_at'] ?? null);

                $reuse = $this->dispatchApp(
                    'GET',
                    '/password/reset/' . rawurlencode($token),
                    [],
                    [],
                    ['HTTP_HOST' => 'verwaltung.local']
                );

                $this->assertSame('/password/forgot', $reuse['redirect_to']);
                $this->assertSame(
                    'Der Reset-Link ist ungueltig oder abgelaufen.',
                    $reuse['session']['_flash']['error'] ?? null
                );
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
