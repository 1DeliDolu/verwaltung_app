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

    public function testWritesAdminUserManagementAuditEntry(): void
    {
        $service = new AuditLogService(testApp());
        $logPath = $service->adminLogFilePath();
        @unlink($logPath);

        $service->recordAdminUserEvent('update_assignment', [
            'actor' => [
                'id' => 1,
                'name' => 'Admin User',
                'email' => 'admin@verwaltung.local',
                'role_name' => 'admin',
            ],
            'target_user' => [
                'id' => 3,
                'name' => 'Ines Leiter',
                'email' => 'leiter.it@verwaltung.local',
                'role_name' => 'team_leader',
            ],
            'department' => [
                'id' => 2,
                'slug' => 'hr',
                'name' => 'Human Resources',
            ],
            'metadata' => [
                'membership_role' => 'employee',
                'target_email' => 'leiter.it@verwaltung.local',
            ],
        ]);

        $content = file_get_contents($logPath);
        @unlink($logPath);

        $this->assertTrue(is_string($content) && $content !== '');

        $entry = json_decode(trim((string) $content), true);

        $this->assertSame('admin_user_management', $entry['event'] ?? null);
        $this->assertSame('update_assignment', $entry['action'] ?? null);
        $this->assertSame(1, $entry['actor']['id'] ?? null);
        $this->assertSame('leiter.it@verwaltung.local', $entry['target_user']['email'] ?? null);
        $this->assertSame('hr', $entry['department']['slug'] ?? null);
        $this->assertSame('employee', $entry['metadata']['membership_role'] ?? null);
    }

    public function testReadsAdminUserManagementAuditEntriesWithFilters(): void
    {
        $logPath = sys_get_temp_dir() . '/admin-audit-' . uniqid('', true) . '.log';
        $service = new AuditLogService(testApp(), $logPath);

        file_put_contents($service->adminLogFilePath(), implode(PHP_EOL, [
            json_encode([
                'timestamp' => '2026-03-24T10:00:00+00:00',
                'event' => 'admin_user_management',
                'action' => 'reset_password',
                'outcome' => 'success',
                'actor' => ['email' => 'admin@verwaltung.local'],
                'target_user' => ['email' => 'leiter.it@verwaltung.local'],
                'department' => ['slug' => 'it', 'name' => 'IT'],
                'metadata' => ['membership_role' => 'team_leader'],
            ], JSON_UNESCAPED_SLASHES),
            json_encode([
                'timestamp' => '2026-03-24T11:00:00+00:00',
                'event' => 'admin_user_management',
                'action' => 'update_assignment',
                'outcome' => 'failure',
                'reason' => 'Department could not be found.',
                'actor' => ['email' => 'admin@verwaltung.local'],
                'target_user' => ['email' => 'leiter.hr@verwaltung.local'],
                'department' => ['slug' => 'hr', 'name' => 'HR'],
                'metadata' => ['membership_role' => 'employee'],
            ], JSON_UNESCAPED_SLASHES),
        ]) . PHP_EOL);

        $filtered = $service->readAdminUserEvents([
            'action' => 'update_assignment',
            'outcome' => 'failure',
            'search' => 'leiter.hr',
        ]);

        @unlink($logPath);

        $this->assertSame(1, count($filtered));
        $this->assertSame('update_assignment', $filtered[0]['action'] ?? null);
        $this->assertSame('failure', $filtered[0]['outcome'] ?? null);
        $this->assertSame('leiter.hr@verwaltung.local', $filtered[0]['target_user']['email'] ?? null);
    }
}
