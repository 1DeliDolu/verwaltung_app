<?php

declare(strict_types=1);

use App\Services\DatabaseSetupService;

final class DatabaseSetupServiceTest extends TestCase
{
    public function testResolveExecutionModeDefaultsToMigrationsAndSeeds(): void
    {
        $mode = DatabaseSetupService::resolveExecutionMode(false, false);

        $this->assertSame(['migrations' => true, 'seeds' => true], $mode);
    }

    public function testResolveExecutionModeRejectsConflictingFlags(): void
    {
        $this->expectException(static function (): void {
            DatabaseSetupService::resolveExecutionMode(true, true);
        });
    }

    public function testFreshSetupIsRejectedOutsideTestingOrCi(): void
    {
        $this->expectException(static function (): void {
            DatabaseSetupService::assertFreshAllowed('local', false);
        });
    }

    public function testFreshSetupIsAllowedForTestingEnvironment(): void
    {
        DatabaseSetupService::assertFreshAllowed('testing', false);
        $this->assertTrue(true);
    }
}
