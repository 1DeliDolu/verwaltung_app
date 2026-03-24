<?php

declare(strict_types=1);

use App\Services\AuditLogService;

final class AuditLogServiceTest extends TestCase
{
    public function testWritesPersonnelDocumentAuditEntry(): void
    {
        $logPath = sys_get_temp_dir() . '/personnel-audit-' . uniqid('', true) . '.log';
        $service = new AuditLogService(testApp(), $logPath);

        $service->recordPersonnelDocumentEvent('download', [
            'actor' => [
                'id' => 7,
                'name' => 'Hanna Personal',
                'email' => 'hanna.personal@verwaltung.local',
                'role_name' => 'team_leader',
            ],
            'department' => [
                'id' => 3,
                'slug' => 'hr',
                'name' => 'Personal',
            ],
            'employee' => [
                'id' => 12,
                'employee_number' => 'HR-2026-0012',
                'full_name' => 'Nina Beispiel',
            ],
            'document' => [
                'id' => 18,
                'employee_id' => 12,
                'original_name' => 'vertrag.pdf',
                'stored_name' => '1710000000-vertrag.pdf',
                'file_path' => 'employees/HR-2026-0012/1710000000-vertrag.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 2048,
            ],
        ]);

        $content = file_get_contents($logPath);
        @unlink($logPath);

        $this->assertTrue(is_string($content) && $content !== '');

        $entry = json_decode(trim((string) $content), true);

        $this->assertSame('personnel_document_access', $entry['event'] ?? null);
        $this->assertSame('download', $entry['action'] ?? null);
        $this->assertSame('success', $entry['outcome'] ?? null);
        $this->assertSame(7, $entry['actor']['id'] ?? null);
        $this->assertSame('hr', $entry['department']['slug'] ?? null);
        $this->assertSame('HR-2026-0012', $entry['employee']['employee_number'] ?? null);
        $this->assertSame('vertrag.pdf', $entry['document']['original_name'] ?? null);
    }
}
