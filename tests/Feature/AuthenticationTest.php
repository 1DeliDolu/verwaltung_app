<?php

declare(strict_types=1);

use App\Models\User;

final class AuthenticationTest extends TestCase
{
    public function testLoginRedirectsBackWithGenericErrorForInvalidCredentials(): void
    {
        $result = $this->dispatchApp(
            'POST',
            '/login',
            ['_csrf_token' => 'login-token'],
            [
                '_token' => 'login-token',
                'email' => 'admin@verwaltung.local',
                'password' => 'wrong-password',
            ],
            ['REMOTE_ADDR' => '203.0.113.10']
        );

        $this->assertSame('/login', $result['redirect_to']);
        $this->assertSame('E-Mail oder Passwort ist ungueltig.', $result['session']['_flash']['error'] ?? null);
        $this->assertSame('admin@verwaltung.local', $result['session']['_flash']['old_email'] ?? null);
    }

    public function testLoginBlocksAfterTooManyFailedAttemptsFromSameIpAndEmail(): void
    {
        $this->withEnv([
            'AUTH_LOGIN_MAX_ATTEMPTS' => '3',
            'AUTH_LOGIN_DECAY_SECONDS' => '900',
        ], function (): void {
            $this->withDatabaseTransaction(function (): void {
                $ipAddress = '203.0.113.20';
                $lockoutMessage = 'Zu viele Anmeldeversuche. Bitte in 15 Minuten erneut versuchen.';

                for ($attempt = 1; $attempt <= 3; $attempt++) {
                    $result = $this->dispatchApp(
                        'POST',
                        '/login',
                        ['_csrf_token' => 'login-token'],
                        [
                            '_token' => 'login-token',
                            'email' => 'admin@verwaltung.local',
                            'password' => 'wrong-password',
                        ],
                        ['REMOTE_ADDR' => $ipAddress]
                    );

                    $this->assertSame('/login', $result['redirect_to']);
                    $expectedMessage = $attempt < 3
                        ? 'E-Mail oder Passwort ist ungueltig.'
                        : $lockoutMessage;
                    $this->assertSame($expectedMessage, $result['session']['_flash']['error'] ?? null);
                }

                $blocked = $this->dispatchApp(
                    'POST',
                    '/login',
                    ['_csrf_token' => 'login-token'],
                    [
                        '_token' => 'login-token',
                        'email' => 'admin@verwaltung.local',
                        'password' => 'D0cker!123',
                    ],
                    ['REMOTE_ADDR' => $ipAddress]
                );

                $this->assertSame('/login', $blocked['redirect_to']);
                $this->assertSame($lockoutMessage, $blocked['session']['_flash']['error'] ?? null);
            });
        });
    }

    public function testAdminLoginRequiresEmailChallengeBeforeSessionCreation(): void
    {
        $capturePath = $this->temporaryPath('login-challenge-');

        $this->withEnv([
            'MAIL_CAPTURE_PATH' => $capturePath,
            'AUTH_MFA_EMAIL_CHALLENGE_ROLES' => 'admin',
            'AUTH_MFA_EMAIL_CHALLENGE_EXPIRE_SECONDS' => '600',
        ], function () use ($capturePath): void {
            $login = $this->dispatchApp(
                'POST',
                '/login',
                ['_csrf_token' => 'login-token'],
                [
                    '_token' => 'login-token',
                    'email' => 'admin@verwaltung.local',
                    'password' => 'D0cker!123',
                ],
                [
                    'REMOTE_ADDR' => '203.0.113.80',
                    'HTTP_USER_AGENT' => 'AuthenticationTest/1.0',
                ]
            );

            $this->assertSame('/login/challenge', $login['redirect_to']);
            $this->assertSame(null, $login['session']['auth_user'] ?? null);
            $this->assertSame('admin@verwaltung.local', $login['session']['auth_pending_mfa']['email'] ?? null);

            $messages = $this->capturedMessages($capturePath);
            $this->assertSame(1, count($messages));
            $this->assertSame(['admin@verwaltung.local'], $messages[0]['to'] ?? []);
            $this->assertSame('Anmeldecode fuer Verwaltung App', $messages[0]['subject'] ?? null);
            $code = $this->extractLoginCode((string) ($messages[0]['text_body'] ?? ''));

            $challengePage = $this->dispatchApp('GET', '/login/challenge', $login['session']);
            $this->assertSame(200, $challengePage['status']);
            $this->assertStringContains('Anmeldecode bestaetigen', $challengePage['content']);

            $verified = $this->dispatchApp(
                'POST',
                '/login/challenge',
                $challengePage['session'],
                [
                    '_token' => (string) ($challengePage['session']['_csrf_token'] ?? ''),
                    'code' => $code,
                ]
            );

            $this->assertSame('/dashboard', $verified['redirect_to']);
            $this->assertSame('admin@verwaltung.local', $verified['session']['auth_user']['email'] ?? null);
            $this->assertSame(null, $verified['session']['auth_pending_mfa'] ?? null);
        });
    }

    public function testLoginChallengeBlocksAfterTooManyInvalidCodes(): void
    {
        $capturePath = $this->temporaryPath('login-challenge-');

        $this->withEnv([
            'MAIL_CAPTURE_PATH' => $capturePath,
            'AUTH_MFA_EMAIL_CHALLENGE_ROLES' => 'admin',
            'AUTH_MFA_EMAIL_CHALLENGE_EXPIRE_SECONDS' => '600',
            'AUTH_MFA_EMAIL_CHALLENGE_MAX_ATTEMPTS' => '3',
            'AUTH_MFA_EMAIL_CHALLENGE_DECAY_SECONDS' => '900',
        ], function () use ($capturePath): void {
            $this->withDatabaseTransaction(function (\PDO $pdo) use ($capturePath): void {
                $login = $this->dispatchApp(
                    'POST',
                    '/login',
                    ['_csrf_token' => 'login-token'],
                    [
                        '_token' => 'login-token',
                        'email' => 'admin@verwaltung.local',
                        'password' => 'D0cker!123',
                    ],
                    [
                        'REMOTE_ADDR' => '203.0.113.82',
                        'HTTP_USER_AGENT' => 'AuthenticationTest/1.1',
                    ]
                );

                $this->assertSame('/login/challenge', $login['redirect_to']);

                $messages = $this->capturedMessages($capturePath);
                $this->assertSame(1, count($messages));
                $code = $this->extractLoginCode((string) ($messages[0]['text_body'] ?? ''));
                $wrongCode = $code === '999999' ? '000000' : '999999';
                $lockoutMessage = 'Zu viele falsche Anmeldecodes. Bitte in 15 Minuten erneut versuchen.';

                $challengePage = $this->dispatchApp('GET', '/login/challenge', $login['session']);
                $this->assertSame(200, $challengePage['status']);

                $session = $challengePage['session'];

                for ($attempt = 1; $attempt <= 3; $attempt++) {
                    $result = $this->dispatchApp(
                        'POST',
                        '/login/challenge',
                        $session,
                        [
                            '_token' => (string) ($session['_csrf_token'] ?? ''),
                            'code' => $wrongCode,
                        ]
                    );

                    $this->assertSame('/login/challenge', $result['redirect_to']);

                    $expectedMessage = $attempt < 3
                        ? 'Der Anmeldecode ist ungueltig oder abgelaufen.'
                        : $lockoutMessage;
                    $this->assertSame($expectedMessage, $result['session']['_flash']['error'] ?? null);
                    $this->assertSame(null, $result['session']['auth_user'] ?? null);
                    $this->assertSame('admin@verwaltung.local', $result['session']['auth_pending_mfa']['email'] ?? null);

                    $session = $result['session'];
                }

                $blocked = $this->dispatchApp(
                    'POST',
                    '/login/challenge',
                    $session,
                    [
                        '_token' => (string) ($session['_csrf_token'] ?? ''),
                        'code' => $code,
                    ]
                );

                $this->assertSame('/login/challenge', $blocked['redirect_to']);
                $this->assertSame($lockoutMessage, $blocked['session']['_flash']['error'] ?? null);
                $this->assertSame(null, $blocked['session']['auth_user'] ?? null);
                $this->assertSame('admin@verwaltung.local', $blocked['session']['auth_pending_mfa']['email'] ?? null);

                $row = $pdo->query(
                    'SELECT failed_attempts, locked_until
                     FROM login_challenge_attempt_limits
                     ORDER BY id DESC
                     LIMIT 1'
                )->fetch() ?: [];

                $this->assertSame(3, (int) ($row['failed_attempts'] ?? 0));
                $this->assertSame(false, ($row['locked_until'] ?? null) === null);
            });
        });
    }

    public function testNonAdminLoginStillCompletesWithoutEmailChallenge(): void
    {
        $capturePath = $this->temporaryPath('login-challenge-');

        $this->withEnv([
            'MAIL_CAPTURE_PATH' => $capturePath,
            'AUTH_MFA_EMAIL_CHALLENGE_ROLES' => 'admin',
        ], function () use ($capturePath): void {
            $this->withDatabaseTransaction(function () use ($capturePath): void {
                $user = User::findByEmail('leiter.it@verwaltung.local') ?? [];
                User::updatePassword((int) ($user['id'] ?? 0), password_hash('N3ues!Passwort123', PASSWORD_DEFAULT));

                $login = $this->dispatchApp(
                    'POST',
                    '/login',
                    ['_csrf_token' => 'login-token'],
                    [
                        '_token' => 'login-token',
                        'email' => 'leiter.it@verwaltung.local',
                        'password' => 'N3ues!Passwort123',
                    ],
                    ['REMOTE_ADDR' => '203.0.113.81']
                );

                $this->assertSame('/dashboard', $login['redirect_to']);
                $this->assertSame('leiter.it@verwaltung.local', $login['session']['auth_user']['email'] ?? null);
                $this->assertSame(null, $login['session']['auth_pending_mfa'] ?? null);
                $this->assertSame([], $this->capturedMessages($capturePath));
            });
        });
    }

    public function testRedirectsPasswordRotationUsersBeforeDashboard(): void
    {
        $result = $this->dispatchApp('GET', '/dashboard', [
            'auth_user' => [
                'id' => 10,
                'email' => 'leiter.it@verwaltung.local',
                'name' => 'Leiter IT',
                'password_change_required_at' => '2026-03-24 12:00:00',
            ],
        ]);

        $this->assertSame('/password/change', $result['redirect_to']);
    }

    public function testAllowsPasswordChangeScreenForRotationUsers(): void
    {
        $result = $this->dispatchApp('GET', '/password/change', [
            'auth_user' => [
                'id' => 10,
                'email' => 'leiter.it@verwaltung.local',
                'name' => 'Leiter IT',
                'password_change_required_at' => '2026-03-24 12:00:00',
            ],
        ]);

        $this->assertSame(200, $result['status']);
        $this->assertStringContains('Passwort aendern', $result['content']);
    }

    public function testAdminMayOpenUserAuditScreen(): void
    {
        $result = $this->dispatchApp('GET', '/users/audit', [
            'auth_user' => [
                'id' => 1,
                'email' => 'admin@verwaltung.local',
                'name' => 'Admin',
                'role_name' => 'admin',
                'password_change_required_at' => null,
            ],
        ]);

        $this->assertSame(200, $result['status']);
        $this->assertStringContains('User Management Audit', $result['content']);
    }

    public function testAdminMayOpenCentralAuditDashboard(): void
    {
        $result = $this->dispatchApp('GET', '/audit', [
            'auth_user' => [
                'id' => 1,
                'email' => 'admin@verwaltung.local',
                'name' => 'Admin',
                'role_name' => 'admin',
                'password_change_required_at' => null,
            ],
        ]);

        $this->assertSame(200, $result['status']);
        $this->assertStringContains('Zentrales Audit Dashboard', $result['content']);
        $this->assertStringContains('Letzte 7 Tage', $result['content']);
        $this->assertStringContains('Top Aktionen nach Quelle', $result['content']);
        $this->assertStringContains('Aktivste Nutzer', $result['content']);
        $this->assertStringContains('Failure Heatmap nach Quelle', $result['content']);
        $this->assertStringContains('/audit?source=admin_user', $result['content']);
        $this->assertStringContains('/audit?source=task', $result['content']);
    }

    public function testAdminMayExportCentralAuditDashboardCsv(): void
    {
        $result = $this->dispatchApp('GET', '/audit?format=csv', [
            'auth_user' => [
                'id' => 1,
                'email' => 'admin@verwaltung.local',
                'name' => 'Admin',
                'role_name' => 'admin',
                'password_change_required_at' => null,
            ],
        ]);

        $this->assertSame(200, $result['status']);
        $this->assertStringContains('timestamp,source,action,outcome,actor_email,subject,context,reason,detail_url', $result['content']);
    }

    public function testAdminMayExportUserAuditCsv(): void
    {
        $result = $this->dispatchApp('GET', '/users/audit?format=csv', [
            'auth_user' => [
                'id' => 1,
                'email' => 'admin@verwaltung.local',
                'name' => 'Admin',
                'role_name' => 'admin',
                'password_change_required_at' => null,
            ],
        ]);

        $this->assertSame(200, $result['status']);
        $this->assertStringContains('timestamp,action,outcome,actor_email,target_user_email,department,membership_role,reason', $result['content']);
    }

    public function testAuthenticatedUserMayOpenMailAuditScreen(): void
    {
        $user = $this->userByEmail('leiter.it@verwaltung.local');
        $result = $this->dispatchApp('GET', '/mail/audit', [
            'auth_user' => $user,
        ]);

        $this->assertSame(200, $result['status']);
        $this->assertStringContains('Mail Activity Audit', $result['content']);
    }

    public function testAuthenticatedUserMayExportMailAuditCsv(): void
    {
        $user = $this->userByEmail('leiter.it@verwaltung.local');
        $result = $this->dispatchApp('GET', '/mail/audit?format=csv', [
            'auth_user' => $user,
        ]);

        $this->assertSame(200, $result['status']);
        $this->assertStringContains('timestamp,action,outcome,actor_email,mail_id,subject,sender_email,recipients,folder,reason', $result['content']);
    }

    private function extractLoginCode(string $body): string
    {
        if (preg_match('/\b(\d{6})\b/', $body, $matches) !== 1) {
            throw new RuntimeException('Login code could not be extracted from the captured mail.');
        }

        return $matches[1];
    }
}
