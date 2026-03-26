<?php

declare(strict_types=1);

final class AuditWeeklyReportTest extends TestCase
{
    public function testAdminMaySendWeeklyAuditReportIgnoringCurrentDashboardFilters(): void
    {
        $capturePath = sys_get_temp_dir() . '/audit-weekly-report-' . uniqid('', true) . '.jsonl';
        $this->withMailEnvironment([
            'MAIL_CAPTURE_PATH' => $capturePath,
            'MAIL_AUDIT_REPORT_NOW' => '2030-04-07 09:00:00',
            'MAIL_AUDIT_REPORT_RECIPIENTS' => 'revision@verwaltung.demo,security@verwaltung.demo',
        ], function () use ($capturePath): void {
            $this->withAuditLogFixtures([
                'admin-user-management.log' => [
                    [
                        'timestamp' => '2030-04-02T08:00:00+00:00',
                        'event' => 'admin_user_management',
                        'action' => 'update_assignment',
                        'outcome' => 'failure',
                        'reason' => 'Role mapping missing.',
                        'actor' => ['email' => 'admin@verwaltung.local'],
                        'target_user' => ['email' => 'leiter.it@verwaltung.local'],
                        'department' => ['slug' => 'it', 'name' => 'IT'],
                        'metadata' => ['membership_role' => 'team_leader'],
                    ],
                ],
                'task-workflow.log' => [
                    [
                        'timestamp' => '2030-04-03T09:00:00+00:00',
                        'event' => 'task_workflow',
                        'action' => 'update_status',
                        'outcome' => 'success',
                        'actor' => ['email' => 'leiter.it@verwaltung.local'],
                        'task' => ['id' => 11, 'title' => 'Server Rollout'],
                        'department' => ['slug' => 'it', 'name' => 'IT'],
                        'metadata' => ['status_from' => 'open', 'status_to' => 'done'],
                    ],
                ],
                'mail-activity.log' => [
                    [
                        'timestamp' => '2030-04-04T10:30:00+00:00',
                        'event' => 'mail_activity',
                        'action' => 'send_mail',
                        'outcome' => 'success',
                        'actor' => ['email' => 'admin@verwaltung.local'],
                        'mail' => [
                            'id' => 99,
                            'subject' => 'Budget Review',
                            'sender_email' => 'admin@verwaltung.local',
                            'recipients' => ['leitung@verwaltung.local'],
                        ],
                        'metadata' => ['folder' => 'sent', 'recipient_count' => 1],
                    ],
                ],
                'calendar-activity.log' => [
                    [
                        'timestamp' => '2030-03-29T11:00:00+00:00',
                        'event' => 'calendar_activity',
                        'action' => 'create_event',
                        'outcome' => 'success',
                        'actor' => ['email' => 'admin@verwaltung.local'],
                        'calendar_event' => [
                            'id' => 7,
                            'title' => 'Old Event',
                            'department_names' => ['IT'],
                        ],
                    ],
                ],
            ], function () use ($capturePath): void {
                $admin = $this->userByEmail('admin@verwaltung.local');
                $dashboard = $this->dispatchApp('GET', '/audit?source=task', [
                    'auth_user' => $admin,
                ]);

                $result = $this->dispatchApp('POST', '/audit/reports/weekly/send', $dashboard['session'], [
                    '_token' => (string) ($dashboard['session']['_csrf_token'] ?? ''),
                    'return_to' => '/audit?source=task',
                ]);

                $this->assertSame('/audit?source=task', $result['redirect_to']);
                $this->assertSame('Audit-Wochenreport wurde an 2 Empfaenger gesendet.', $result['session']['_flash']['success'] ?? null);

                $lines = file($capturePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
                $this->assertSame(1, count($lines));

                $message = json_decode((string) $lines[0], true);

                $this->assertSame('audit-weekly-report', $message['template'] ?? null);
                $this->assertSame(
                    ['revision@verwaltung.demo', 'security@verwaltung.demo'],
                    $message['to'] ?? []
                );
                $this->assertSame('Audit Wochenreport | 2030-04-01 bis 2030-04-07', $message['subject'] ?? null);
                $this->assertStringContains('Events gesamt: 3', (string) ($message['text_body'] ?? ''));
                $this->assertStringContains('User Management: 1', (string) ($message['text_body'] ?? ''));
                $this->assertStringContains('Mail: 1', (string) ($message['text_body'] ?? ''));

                $attachments = (array) ($message['attachments'] ?? []);
                $this->assertSame(1, count($attachments));
                $this->assertSame('audit-weekly-report-2030-04-01-to-2030-04-07.csv', $attachments[0]['name'] ?? null);

                $csv = base64_decode((string) ($attachments[0]['content_base64'] ?? ''), true);
                $this->assertTrue(is_string($csv) && $csv !== '');
                $this->assertStringContains('timestamp,source,action,outcome,actor_email,subject,context,reason,detail_url', $csv);
                $this->assertStringContains('admin_user', $csv);
                $this->assertStringContains('task', $csv);
                $this->assertStringContains('mail', $csv);
                $this->assertStringContains('Budget Review', $csv);
            });
        });
    }

    public function testNonAdminCannotSendWeeklyAuditReport(): void
    {
        $capturePath = sys_get_temp_dir() . '/audit-weekly-report-' . uniqid('', true) . '.jsonl';
        $this->withMailEnvironment([
            'MAIL_CAPTURE_PATH' => $capturePath,
            'MAIL_AUDIT_REPORT_NOW' => '2030-04-07 09:00:00',
        ], function () use ($capturePath): void {
            $user = $this->userByEmail('leiter.it@verwaltung.local');
            $result = $this->dispatchApp('POST', '/audit/reports/weekly/send', [
                'auth_user' => $user,
                '_csrf_token' => 'weekly-report-token',
            ], [
                '_token' => 'weekly-report-token',
                'return_to' => '/audit',
            ]);

            $this->assertSame(403, $result['status']);
            $this->assertSame(null, $result['redirect_to']);
            $this->assertSame(false, is_file($capturePath));
        });
    }

    private function withMailEnvironment(array $overrides, callable $callback): void
    {
        $keys = array_keys($overrides);
        $originals = [];

        foreach ($keys as $key) {
            $originals[$key] = $_ENV[$key] ?? null;
            $_ENV[$key] = (string) $overrides[$key];
            $_SERVER[$key] = (string) $overrides[$key];
            putenv($key . '=' . (string) $overrides[$key]);
        }

        try {
            $callback();
        } finally {
            foreach ($originals as $key => $value) {
                if ($value === null) {
                    unset($_ENV[$key], $_SERVER[$key]);
                    putenv($key);
                    continue;
                }

                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                putenv($key . '=' . $value);
            }
        }
    }

    private function withAuditLogFixtures(array $fixtures, callable $callback): void
    {
        $logDirectory = BASE_PATH . '/storage/logs';
        $backups = [];

        foreach ($fixtures as $filename => $entries) {
            $path = $logDirectory . '/' . $filename;
            $backups[$path] = is_file($path) ? file_get_contents($path) : false;

            $lines = array_map(
                static fn (array $entry): string => (string) json_encode($entry, JSON_UNESCAPED_SLASHES),
                $entries
            );

            file_put_contents($path, implode(PHP_EOL, $lines) . PHP_EOL);
        }

        try {
            $callback();
        } finally {
            foreach ($backups as $path => $content) {
                if ($content === false) {
                    @unlink($path);
                    continue;
                }

                file_put_contents($path, $content);
            }
        }
    }
}
