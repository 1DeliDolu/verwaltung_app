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

    public function testDepartmentsIndexShowsConfiguredSummaryStats(): void
    {
        $user = $this->userByEmail('leiter.it@verwaltung.local');
        $result = $this->dispatchApp('GET', '/departments', [
            'auth_user' => $user,
        ]);

        $this->assertSame(200, $result['status']);
        $this->assertStringContains('Verwaltete Konten', $result['content']);
        $this->assertStringContains('Dokumente', $result['content']);
        $this->assertStringContains('Dateien', $result['content']);
    }

    public function testDepartmentDetailShowsConfiguredHrSummaryStats(): void
    {
        $user = $this->userByEmail('leiter.hr@verwaltung.local');
        $result = $this->dispatchApp('GET', '/departments/hr', [
            'auth_user' => $user,
        ]);

        $this->assertSame(200, $result['status']);
        $this->assertStringContains('Bereichsuebersicht', $result['content']);
        $this->assertStringContains('Mitarbeiter', $result['content']);
        $this->assertStringContains('Personalakten', $result['content']);
    }

    public function testDepartmentDetailStillRendersSpecializedPartialWhenNoConfigPlaybookExists(): void
    {
        $user = $this->userByEmail('leiter.it@verwaltung.local');
        $result = $this->dispatchApp('GET', '/departments/it', [
            'auth_user' => $user,
        ]);

        $this->assertSame(200, $result['status']);
        $this->assertStringContains('Technische Leitlinien', $result['content']);
        $this->assertStringContains('Provisionierung', $result['content']);
    }

    public function testDepartmentDetailDoesNotShowUnconfiguredSummaryStats(): void
    {
        $user = $this->userByEmail('leiter.marketing@verwaltung.local');
        $result = $this->dispatchApp('GET', '/departments/marketing', [
            'auth_user' => $user,
        ]);

        $this->assertSame(200, $result['status']);
        $this->assertStringContains('Kampagnensteuerung', $result['content']);
        $this->assertStringContains('Briefings und Zielgruppenannahmen je Kampagne dokumentieren', $result['content']);
        $this->assertTrue(!str_contains($result['content'], 'Verwaltete Konten'));
        $this->assertTrue(!str_contains($result['content'], 'Personalakten'));
    }
}
