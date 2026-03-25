<?php

declare(strict_types=1);

final class AuthenticationTest extends TestCase
{
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
