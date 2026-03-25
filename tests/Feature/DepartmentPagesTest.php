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
}
