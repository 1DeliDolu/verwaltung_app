<?php

declare(strict_types=1);

final class DepartmentPagesTest extends TestCase
{
    public function testRedirectsGuestsAwayFromCalendar(): void
    {
        $result = $this->dispatchApp('GET', '/calendar');

        $this->assertSame('/login', $result['redirect_to']);
        $this->assertSame('Du musst dich anmelden, um diese Seite aufzurufen.', $result['session']['_flash']['error'] ?? null);
    }

    public function testGuestMayStillOpenNewsPage(): void
    {
        $result = $this->dispatchApp('GET', '/news');

        $this->assertSame(200, $result['status']);
        $this->assertSame(null, $result['redirect_to']);
    }

    public function testAuthenticatedUserMayOpenCalendarAudit(): void
    {
        $user = $this->userByEmail('leiter.it@verwaltung.local');
        $result = $this->dispatchApp('GET', '/calendar/audit', [
            'auth_user' => $user,
        ]);

        $this->assertSame(200, $result['status']);
        $this->assertStringContains('Calendar Activity Audit', $result['content']);
    }

    public function testAuthenticatedUserMayExportCalendarAuditCsv(): void
    {
        $user = $this->userByEmail('leiter.it@verwaltung.local');
        $result = $this->dispatchApp('GET', '/calendar/audit?format=csv', [
            'auth_user' => $user,
        ]);

        $this->assertSame(200, $result['status']);
        $this->assertStringContains('timestamp,action,outcome,actor_email,event_id,title,starts_at,ends_at,departments,reason', $result['content']);
    }
}
