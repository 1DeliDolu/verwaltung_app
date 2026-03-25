<?php

declare(strict_types=1);

use App\Services\TaskService;

final class TaskServiceTest extends TestCase
{
    public function testWorkerMayMoveOpenTaskToInProgress(): void
    {
        TaskService::assertStatusTransition('open', 'in_progress', false);
        $this->assertTrue(true);
    }

    public function testWorkerMayNotReopenDoneTask(): void
    {
        $this->expectException(static function (): void {
            TaskService::assertStatusTransition('done', 'open', false);
        });
    }

    public function testManagerMayReopenDoneTask(): void
    {
        TaskService::assertStatusTransition('done', 'open', true);
        $this->assertTrue(true);
    }

    public function testWorkerMayNotResetInProgressTaskToOpen(): void
    {
        $this->expectException(static function (): void {
            TaskService::assertStatusTransition('in_progress', 'open', false);
        });
    }
}
