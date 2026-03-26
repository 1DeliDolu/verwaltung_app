<?php

declare(strict_types=1);

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
}
