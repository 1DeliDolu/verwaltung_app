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

    public function testReadsAdminAuditEntriesWithDateRangeAndExportsCsv(): void
    {
        $logPath = sys_get_temp_dir() . '/admin-audit-' . uniqid('', true) . '.log';
        $service = new AuditLogService(testApp(), $logPath);

        file_put_contents($service->adminLogFilePath(), implode(PHP_EOL, [
            json_encode([
                'timestamp' => '2026-03-20T10:00:00+00:00',
                'event' => 'admin_user_management',
                'action' => 'reset_password',
                'outcome' => 'success',
                'actor' => ['email' => 'admin@verwaltung.local'],
                'target_user' => ['email' => 'leiter.it@verwaltung.local'],
                'department' => ['slug' => 'it', 'name' => 'IT'],
                'metadata' => ['membership_role' => 'team_leader'],
            ], JSON_UNESCAPED_SLASHES),
            json_encode([
                'timestamp' => '2026-03-25T10:00:00+00:00',
                'event' => 'admin_user_management',
                'action' => 'update_assignment',
                'outcome' => 'success',
                'actor' => ['email' => 'admin@verwaltung.local'],
                'target_user' => ['email' => 'leiter.hr@verwaltung.local'],
                'department' => ['slug' => 'hr', 'name' => 'HR'],
                'metadata' => ['membership_role' => 'employee'],
            ], JSON_UNESCAPED_SLASHES),
        ]) . PHP_EOL);

        $filtered = $service->readAdminUserEvents([
            'date_from' => '2026-03-24',
            'date_to' => '2026-03-26',
        ]);
        $csv = $service->adminUserEventsAsCsv($filtered);

        @unlink($logPath);

        $this->assertSame(1, count($filtered));
        $this->assertSame('leiter.hr@verwaltung.local', $filtered[0]['target_user']['email'] ?? null);
        $this->assertStringContains('timestamp,action,outcome,actor_email,target_user_email,department,membership_role,reason', $csv);
        $this->assertStringContains('leiter.hr@verwaltung.local', $csv);
    }

    public function testWritesTaskWorkflowAuditEntry(): void
    {
        $logPath = sys_get_temp_dir() . '/task-audit-' . uniqid('', true) . '.log';
        $service = new AuditLogService(testApp(), $logPath);

        $service->recordTaskWorkflowEvent('update_status', [
            'actor' => [
                'id' => 5,
                'name' => 'Ina Leiter',
                'email' => 'leiter.it@verwaltung.local',
                'role_name' => 'team_leader',
            ],
            'task' => [
                'id' => 22,
                'title' => 'Server Migration',
                'status' => 'in_progress',
                'priority' => 'high',
            ],
            'department' => [
                'id' => 1,
                'slug' => 'it',
                'name' => 'IT',
            ],
            'metadata' => [
                'status_from' => 'open',
                'status_to' => 'in_progress',
            ],
        ]);

        $content = file_get_contents($logPath);
        @unlink($logPath);

        $this->assertTrue(is_string($content) && $content !== '');

        $entry = json_decode(trim((string) $content), true);

        $this->assertSame('task_workflow', $entry['event'] ?? null);
        $this->assertSame('update_status', $entry['action'] ?? null);
        $this->assertSame('leiter.it@verwaltung.local', $entry['actor']['email'] ?? null);
        $this->assertSame('Server Migration', $entry['task']['title'] ?? null);
        $this->assertSame('it', $entry['department']['slug'] ?? null);
        $this->assertSame('in_progress', $entry['metadata']['status_to'] ?? null);
    }

    public function testReadsTaskWorkflowAuditEntriesWithFiltersAndExportsCsv(): void
    {
        $logPath = sys_get_temp_dir() . '/task-audit-' . uniqid('', true) . '.log';
        $service = new AuditLogService(testApp(), $logPath);

        file_put_contents($service->taskAuditLogFilePath(), implode(PHP_EOL, [
            json_encode([
                'timestamp' => '2026-03-20T10:00:00+00:00',
                'event' => 'task_workflow',
                'action' => 'create_task',
                'outcome' => 'success',
                'actor' => ['email' => 'leiter.it@verwaltung.local'],
                'task' => ['id' => 7, 'title' => 'Launch Checklist'],
                'department' => ['id' => 1, 'slug' => 'it', 'name' => 'IT'],
                'metadata' => ['status_to' => 'open'],
            ], JSON_UNESCAPED_SLASHES),
            json_encode([
                'timestamp' => '2026-03-25T10:00:00+00:00',
                'event' => 'task_workflow',
                'action' => 'update_status',
                'outcome' => 'failure',
                'reason' => 'Task status transition is not allowed.',
                'actor' => ['email' => 'mitarbeiter.it@verwaltung.local'],
                'task' => ['id' => 8, 'title' => 'Client Rollout'],
                'department' => ['id' => 1, 'slug' => 'it', 'name' => 'IT'],
                'metadata' => ['status_from' => 'open', 'status_to' => 'done'],
            ], JSON_UNESCAPED_SLASHES),
            json_encode([
                'timestamp' => '2026-03-25T11:00:00+00:00',
                'event' => 'task_workflow',
                'action' => 'add_comment',
                'outcome' => 'success',
                'actor' => ['email' => 'leiter.hr@verwaltung.local'],
                'task' => ['id' => 9, 'title' => 'Interview Paket'],
                'department' => ['id' => 2, 'slug' => 'hr', 'name' => 'HR'],
                'metadata' => ['comment_preview' => 'Termin bestaetigt'],
            ], JSON_UNESCAPED_SLASHES),
        ]) . PHP_EOL);

        $filtered = $service->readTaskWorkflowEvents([
            'action' => 'update_status',
            'outcome' => 'failure',
            'department_id' => 1,
            'date_from' => '2026-03-24',
            'date_to' => '2026-03-26',
            'search' => 'client',
        ]);
        $csv = $service->taskWorkflowEventsAsCsv($filtered);

        @unlink($logPath);

        $this->assertSame(1, count($filtered));
        $this->assertSame('Client Rollout', $filtered[0]['task']['title'] ?? null);
        $this->assertStringContains('timestamp,action,outcome,actor_email,department,task_id,task_title,status_from,status_to,reason', $csv);
        $this->assertStringContains('Client Rollout', $csv);
    }

    public function testWritesMailActivityAuditEntry(): void
    {
        $logPath = sys_get_temp_dir() . '/mail-audit-' . uniqid('', true) . '.log';
        $service = new AuditLogService(testApp(), $logPath);

        $service->recordMailActivityEvent('archive_mail', [
            'actor' => [
                'id' => 9,
                'name' => 'Ina Leiter',
                'email' => 'leiter.it@verwaltung.local',
                'role_name' => 'team_leader',
            ],
            'mail' => [
                'id' => 17,
                'subject' => 'Projektstatus',
                'sender_email' => 'admin@verwaltung.local',
                'recipients' => ['leiter.it@verwaltung.local'],
            ],
            'metadata' => [
                'folder' => 'inbox',
                'recipient_count' => 1,
            ],
        ]);

        $content = file_get_contents($logPath);
        @unlink($logPath);

        $this->assertTrue(is_string($content) && $content !== '');

        $entry = json_decode(trim((string) $content), true);

        $this->assertSame('mail_activity', $entry['event'] ?? null);
        $this->assertSame('archive_mail', $entry['action'] ?? null);
        $this->assertSame('Projektstatus', $entry['mail']['subject'] ?? null);
        $this->assertSame('inbox', $entry['metadata']['folder'] ?? null);
    }

    public function testReadsMailActivityEntriesWithFiltersAndExportsCsv(): void
    {
        $logPath = sys_get_temp_dir() . '/mail-audit-' . uniqid('', true) . '.log';
        $service = new AuditLogService(testApp(), $logPath);

        file_put_contents($service->mailAuditLogFilePath(), implode(PHP_EOL, [
            json_encode([
                'timestamp' => '2026-03-20T10:00:00+00:00',
                'event' => 'mail_activity',
                'action' => 'send_mail',
                'outcome' => 'success',
                'actor' => ['email' => 'leiter.it@verwaltung.local'],
                'mail' => ['id' => 10, 'subject' => 'Projektstatus', 'sender_email' => 'leiter.it@verwaltung.local', 'recipients' => ['leiter.hr@verwaltung.local']],
                'metadata' => ['folder' => 'sent'],
            ], JSON_UNESCAPED_SLASHES),
            json_encode([
                'timestamp' => '2026-03-25T10:00:00+00:00',
                'event' => 'mail_activity',
                'action' => 'download_attachment',
                'outcome' => 'failure',
                'reason' => 'Attachment not found.',
                'actor' => ['email' => 'leiter.it@verwaltung.local'],
                'mail' => ['id' => 11, 'subject' => 'Budget', 'sender_email' => 'admin@verwaltung.local', 'recipients' => ['leiter.it@verwaltung.local']],
                'metadata' => ['folder' => 'mailbox', 'attachment_name' => 'budget.pdf'],
            ], JSON_UNESCAPED_SLASHES),
        ]) . PHP_EOL);

        $filtered = $service->readMailActivityEvents([
            'action' => 'download_attachment',
            'outcome' => 'failure',
            'date_from' => '2026-03-24',
            'date_to' => '2026-03-26',
            'search' => 'budget',
        ]);
        $csv = $service->mailActivityEventsAsCsv($filtered);

        @unlink($logPath);

        $this->assertSame(1, count($filtered));
        $this->assertSame('Budget', $filtered[0]['mail']['subject'] ?? null);
        $this->assertStringContains('timestamp,action,outcome,actor_email,mail_id,subject,sender_email,recipients,folder,reason', $csv);
        $this->assertStringContains('Budget', $csv);
    }

    public function testWritesCalendarActivityAuditEntry(): void
    {
        $logPath = sys_get_temp_dir() . '/calendar-audit-' . uniqid('', true) . '.log';
        $service = new AuditLogService(testApp(), $logPath);

        $service->recordCalendarActivityEvent('complete_event', [
            'actor' => [
                'id' => 1,
                'name' => 'Admin',
                'email' => 'admin@verwaltung.local',
                'role_name' => 'admin',
            ],
            'calendar_event' => [
                'id' => 12,
                'title' => 'Sprint Review',
                'location' => 'Raum A',
                'created_by' => 9,
                'department_ids' => [1],
                'department_names' => ['IT'],
            ],
            'metadata' => [
                'starts_at' => '2026-03-26 09:00',
                'ends_at' => '2026-03-26 10:00',
            ],
        ]);

        $content = file_get_contents($logPath);
        @unlink($logPath);

        $this->assertTrue(is_string($content) && $content !== '');

        $entry = json_decode(trim((string) $content), true);

        $this->assertSame('calendar_activity', $entry['event'] ?? null);
        $this->assertSame('complete_event', $entry['action'] ?? null);
        $this->assertSame('Sprint Review', $entry['calendar_event']['title'] ?? null);
        $this->assertSame('IT', $entry['calendar_event']['department_names'][0] ?? null);
    }

    public function testReadsCalendarActivityEntriesWithFiltersAndExportsCsv(): void
    {
        $logPath = sys_get_temp_dir() . '/calendar-audit-' . uniqid('', true) . '.log';
        $service = new AuditLogService(testApp(), $logPath);

        file_put_contents($service->calendarAuditLogFilePath(), implode(PHP_EOL, [
            json_encode([
                'timestamp' => '2026-03-20T10:00:00+00:00',
                'event' => 'calendar_activity',
                'action' => 'create_event',
                'outcome' => 'success',
                'actor' => ['email' => 'leiter.it@verwaltung.local'],
                'calendar_event' => ['id' => 21, 'title' => 'Planung', 'department_ids' => [1], 'department_names' => ['IT']],
                'metadata' => ['starts_at' => '2026-03-21 10:00', 'ends_at' => '2026-03-21 11:00'],
            ], JSON_UNESCAPED_SLASHES),
            json_encode([
                'timestamp' => '2026-03-25T10:00:00+00:00',
                'event' => 'calendar_activity',
                'action' => 'delete_event',
                'outcome' => 'failure',
                'reason' => 'Not allowed to edit this event.',
                'actor' => ['email' => 'leiter.hr@verwaltung.local'],
                'calendar_event' => ['id' => 22, 'title' => 'IT Townhall', 'department_ids' => [1], 'department_names' => ['IT']],
                'metadata' => ['starts_at' => '2026-03-28 14:00', 'ends_at' => '2026-03-28 15:00'],
            ], JSON_UNESCAPED_SLASHES),
        ]) . PHP_EOL);

        $filtered = $service->readCalendarActivityEvents([
            'action' => 'delete_event',
            'outcome' => 'failure',
            'department_id' => 1,
            'date_from' => '2026-03-24',
            'date_to' => '2026-03-26',
            'search' => 'townhall',
        ]);
        $csv = $service->calendarActivityEventsAsCsv($filtered);

        @unlink($logPath);

        $this->assertSame(1, count($filtered));
        $this->assertSame('IT Townhall', $filtered[0]['calendar_event']['title'] ?? null);
        $this->assertStringContains('timestamp,action,outcome,actor_email,event_id,title,starts_at,ends_at,departments,reason', $csv);
        $this->assertStringContains('IT Townhall', $csv);
    }
}
