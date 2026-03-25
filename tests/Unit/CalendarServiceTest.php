<?php

declare(strict_types=1);

use App\Services\CalendarService;

final class CalendarServiceTest extends TestCase
{
    public function testAdminMayManageForeignEvent(): void
    {
        $service = new CalendarService(testApp());

        $this->assertTrue($service->mayManageEvent(
            ['id' => 1, 'role_name' => 'admin'],
            ['created_by' => 99]
        ));
    }

    public function testCreatorMayManageOwnEvent(): void
    {
        $service = new CalendarService(testApp());

        $this->assertTrue($service->mayManageEvent(
            ['id' => 5, 'role_name' => 'employee'],
            ['created_by' => 5]
        ));
    }

    public function testForeignEmployeeMayNotManageEvent(): void
    {
        $service = new CalendarService(testApp());

        $this->assertSame(false, $service->mayManageEvent(
            ['id' => 5, 'role_name' => 'employee'],
            ['created_by' => 9]
        ));
    }
}
