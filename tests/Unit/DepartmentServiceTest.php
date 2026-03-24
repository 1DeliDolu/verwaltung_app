<?php

declare(strict_types=1);

use App\Services\DepartmentService;

final class DepartmentServiceTest extends TestCase
{
    public function testAcceptsManagedPersonRulesForValidInput(): void
    {
        DepartmentService::assertManagedPersonRules(
            'Hanna Personal',
            'hanna.personal@verwaltung.local',
            'StrongPass!2026',
            'StrongPass!2026',
            'employee'
        );

        $this->assertTrue(true);
    }

    public function testRejectsManagedPersonRulesForInvalidEmail(): void
    {
        $this->expectException(static function (): void {
            DepartmentService::assertManagedPersonRules(
                'Hanna Personal',
                'not-an-email',
                'StrongPass!2026',
                'StrongPass!2026',
                'employee'
            );
        });
    }

    public function testRejectsManagedPersonRulesForInvalidRole(): void
    {
        $this->expectException(static function (): void {
            DepartmentService::assertManagedPersonRules(
                'Hanna Personal',
                'hanna.personal@verwaltung.local',
                'StrongPass!2026',
                'StrongPass!2026',
                'admin'
            );
        });
    }

    public function testAcceptsEmployeeProfileRulesForValidInput(): void
    {
        DepartmentService::assertEmployeeProfileRules(
            'active',
            '2026-03-24',
            'BDSG Paragraf 26 / DSGVO Art. 6 Abs. 1 lit. b',
            '2036-03-24'
        );

        $this->assertTrue(true);
    }

    public function testRejectsEmployeeProfileRulesForInvalidStatus(): void
    {
        $this->expectException(static function (): void {
            DepartmentService::assertEmployeeProfileRules(
                'pending',
                '2026-03-24',
                'BDSG Paragraf 26 / DSGVO Art. 6 Abs. 1 lit. b',
                '2036-03-24'
            );
        });
    }

    public function testRejectsEmployeeProfileRulesForInvalidProcessingBasis(): void
    {
        $this->expectException(static function (): void {
            DepartmentService::assertEmployeeProfileRules(
                'active',
                '2026-03-24',
                'Some other legal basis',
                '2036-03-24'
            );
        });
    }
}
