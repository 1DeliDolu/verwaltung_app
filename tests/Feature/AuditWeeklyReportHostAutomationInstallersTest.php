<?php

declare(strict_types=1);

final class AuditWeeklyReportHostAutomationInstallersTest extends TestCase
{
    public function testSystemdInstallerCopiesRenderedServiceAndTimerFiles(): void
    {
        $installDir = sys_get_temp_dir() . '/audit-systemd-install-' . uniqid('', true);
        $tmpDir = sys_get_temp_dir() . '/audit-systemd-installer-tmp-' . uniqid('', true);

        mkdir($tmpDir, 0777, true);

        try {
            $result = $this->runScript(
                [
                    BASE_PATH . '/infra/scripts/install-weekly-audit-report-systemd.sh',
                    $installDir,
                    'www-data',
                    'www-data',
                    'admin@verwaltung.local',
                    'Mon *-*-* 07:00:00',
                ],
                ['TMPDIR' => $tmpDir]
            );

            $this->assertSame(0, $result['exit_code']);
            $this->assertStringContains(
                'Installed verwaltung-weekly-audit-report.service and verwaltung-weekly-audit-report.timer into ' . $installDir,
                $result['stdout']
            );
            $this->assertStringContains(
                'Next step: systemctl daemon-reload && systemctl enable --now verwaltung-weekly-audit-report.timer',
                $result['stdout']
            );

            $servicePath = $installDir . '/verwaltung-weekly-audit-report.service';
            $timerPath = $installDir . '/verwaltung-weekly-audit-report.timer';
            $service = file_get_contents($servicePath);
            $timer = file_get_contents($timerPath);

            $this->assertTrue(is_string($service) && $service !== '');
            $this->assertTrue(is_string($timer) && $timer !== '');
            $this->assertStringContains('User=www-data', $service);
            $this->assertStringContains('Group=www-data', $service);
            $this->assertStringContains(BASE_PATH . '/infra/scripts/send-weekly-audit-report.sh --admin-email=admin@verwaltung.local', $service);
            $this->assertStringContains('OnCalendar=Mon *-*-* 07:00:00', $timer);
            $this->assertSame(false, str_contains($service, '__APP_ROOT__'));
            $this->assertSame(false, str_contains($timer, '__ON_CALENDAR__'));
            $this->assertSame('0644', $this->fileMode($servicePath));
            $this->assertSame('0644', $this->fileMode($timerPath));
            $this->assertSame([], $this->directoryEntries($tmpDir), 'Expected installer temp directory to be cleaned up.');
        } finally {
            $this->removeDirectory($installDir);
            $this->removeDirectory($tmpDir);
        }
    }

    public function testCronInstallerCopiesRenderedCronFile(): void
    {
        $installDir = sys_get_temp_dir() . '/audit-cron-install-' . uniqid('', true);
        $installPath = $installDir . '/verwaltung-weekly-audit-report';
        $tmpDir = sys_get_temp_dir() . '/audit-cron-installer-tmp-' . uniqid('', true);

        mkdir($tmpDir, 0777, true);

        try {
            $result = $this->runScript(
                [
                    BASE_PATH . '/infra/scripts/install-weekly-audit-report-cron.sh',
                    $installPath,
                    'root',
                    'admin@verwaltung.local',
                    '0 7 * * 1',
                    '/var/log/verwaltung-weekly-audit-report.log',
                ],
                ['TMPDIR' => $tmpDir]
            );

            $this->assertSame(0, $result['exit_code']);
            $this->assertStringContains(
                'Installed weekly audit report cron file into ' . $installPath,
                $result['stdout']
            );
            $this->assertStringContains(
                'Next step: verify the host cron daemon loads ' . $installPath,
                $result['stdout']
            );

            $cron = file_get_contents($installPath);

            $this->assertTrue(is_string($cron) && $cron !== '');
            $this->assertStringContains('0 7 * * 1 root cd ' . BASE_PATH, $cron);
            $this->assertStringContains(
                '/usr/bin/env bash ' . BASE_PATH . '/infra/scripts/send-weekly-audit-report.sh --admin-email=admin@verwaltung.local',
                $cron
            );
            $this->assertStringContains('/var/log/verwaltung-weekly-audit-report.log', $cron);
            $this->assertSame(false, str_contains($cron, '__CRON_SCHEDULE__'));
            $this->assertSame('0644', $this->fileMode($installPath));
            $this->assertSame([], $this->directoryEntries($tmpDir), 'Expected installer temp file to be cleaned up.');
        } finally {
            $this->removeDirectory($installDir);
            $this->removeDirectory($tmpDir);
        }
    }

    public function testInstallersFailWhenInstallTargetArgumentIsMissing(): void
    {
        $systemd = $this->runScript([BASE_PATH . '/infra/scripts/install-weekly-audit-report-systemd.sh']);
        $cron = $this->runScript([BASE_PATH . '/infra/scripts/install-weekly-audit-report-cron.sh']);

        $this->assertSame(1, $systemd['exit_code']);
        $this->assertSame(1, $cron['exit_code']);
        $this->assertStringContains('Usage:', $systemd['stderr']);
        $this->assertStringContains('Usage:', $cron['stderr']);
    }

    private function runScript(array $command, array $env = []): array
    {
        $escaped = implode(' ', array_map('escapeshellarg', $command));
        $baseEnv = getenv();
        $processEnv = $env === []
            ? null
            : array_merge(is_array($baseEnv) ? $baseEnv : [], $env);

        $process = proc_open(
            $escaped,
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            BASE_PATH,
            $processEnv
        );

        if (!is_resource($process)) {
            throw new RuntimeException('Installer script could not be started.');
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

    private function fileMode(string $path): string
    {
        return substr(sprintf('%o', fileperms($path)), -4);
    }

    private function directoryEntries(string $path): array
    {
        return array_values(array_diff(scandir($path) ?: [], ['.', '..']));
    }

    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            if (is_file($path)) {
                @unlink($path);
            }

            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($path);
    }
}
