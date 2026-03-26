<?php

declare(strict_types=1);

final class AuditWeeklyReportAutomationTest extends TestCase
{
    public function testWeeklyAuditReportCommandSupportsDryRun(): void
    {
        $result = $this->runWeeklyReportCommand([
            '--dry-run',
            '--admin-email=admin@verwaltung.local',
            '--recipient=revision@verwaltung.demo',
            '--recipient=security@verwaltung.demo',
            '--now=2030-04-07 09:00:00',
        ]);

        $this->assertSame(0, $result['exit_code']);
        $this->assertStringContains('Dry run successful.', $result['stdout']);
        $this->assertStringContains('Admin: admin@verwaltung.local', $result['stdout']);
        $this->assertStringContains('Window: 2030-04-01 bis 2030-04-07', $result['stdout']);
        $this->assertStringContains('revision@verwaltung.demo, security@verwaltung.demo', $result['stdout']);
        $this->assertSame('', trim($result['stderr']));
    }

    public function testWeeklyAuditReportCommandCanCaptureWeeklySend(): void
    {
        $capturePath = sys_get_temp_dir() . '/audit-weekly-command-' . uniqid('', true) . '.jsonl';

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
        ], function () use ($capturePath): void {
            $result = $this->runWeeklyReportCommand([
                '--admin-email=admin@verwaltung.local',
                '--recipient=revision@verwaltung.demo',
                '--capture-path=' . $capturePath,
                '--now=2030-04-07 09:00:00',
            ]);

            $this->assertSame(0, $result['exit_code']);
            $this->assertStringContains('Weekly audit report sent successfully.', $result['stdout']);
            $this->assertStringContains('Attachment: audit-weekly-report-2030-04-01-to-2030-04-07.csv', $result['stdout']);
            $this->assertSame('', trim($result['stderr']));

            $lines = file($capturePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            $this->assertSame(1, count($lines));

            $message = json_decode((string) $lines[0], true);

            $this->assertSame('audit-weekly-report', $message['template'] ?? null);
            $this->assertSame(['revision@verwaltung.demo'], $message['to'] ?? []);

            $attachments = (array) ($message['attachments'] ?? []);
            $this->assertSame(1, count($attachments));

            $csv = base64_decode((string) ($attachments[0]['content_base64'] ?? ''), true);
            $this->assertTrue(is_string($csv) && $csv !== '');
            $this->assertStringContains('admin_user', $csv);
            $this->assertStringContains('task', $csv);
            $this->assertStringContains('mail', $csv);
            $this->assertStringContains('Budget Review', $csv);
        });

        @unlink($capturePath);
    }

    public function testWeeklyAuditReportCommandRejectsNonAdminUser(): void
    {
        $result = $this->runWeeklyReportCommand([
            '--dry-run',
            '--admin-email=leiter.it@verwaltung.local',
        ]);

        $this->assertSame(1, $result['exit_code']);
        $this->assertStringContains('Audit-Report CLI erfordert einen Admin-Benutzer', $result['stderr']);
    }

    private function runWeeklyReportCommand(array $arguments): array
    {
        $command = array_merge(
            [PHP_BINARY, BASE_PATH . '/bin/send-weekly-audit-report.php'],
            $arguments
        );
        $escaped = implode(' ', array_map('escapeshellarg', $command));
        $process = proc_open(
            $escaped,
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            BASE_PATH
        );

        if (!is_resource($process)) {
            throw new RuntimeException('Weekly audit report command could not be started.');
        }

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        return [
            'exit_code' => $exitCode,
            'stdout' => is_string($stdout) ? $stdout : '',
            'stderr' => is_string($stderr) ? $stderr : '',
        ];
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
