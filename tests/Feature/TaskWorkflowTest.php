<?php

declare(strict_types=1);

final class TaskWorkflowTest extends TestCase
{
    public function testRedirectsGuestsAwayFromTasksIndex(): void
    {
        $result = $this->dispatchApp('GET', '/tasks');

        $this->assertSame('/login', $result['redirect_to']);
        $this->assertSame('Du musst dich anmelden, um diese Seite aufzurufen.', $result['session']['_flash']['error'] ?? null);
    }

    public function testUnknownRouteReturnsNotFoundPage(): void
    {
        $result = $this->dispatchApp('GET', '/tasks/does-not-exist/extra');

        $this->assertSame(404, $result['status']);
        $this->assertStringContains('404', $result['content']);
    }
}
