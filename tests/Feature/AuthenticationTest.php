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
}
