<?php

declare(strict_types=1);

final class AuditWeeklyReportHostAutomationAssetsTest extends TestCase
{
    public function testSystemdRendererProducesServiceAndTimerFiles(): void
    {
        $outputDir = sys_get_temp_dir() . '/audit-systemd-' . uniqid('', true);
        mkdir($outputDir, 0777, true);

        $result = $this->runScript([
            BASE_PATH . '/infra/scripts/render-weekly-audit-report-systemd.sh',
            $outputDir,
            'www-data',
            'www-data',
            'admin@verwaltung.local',
            'Mon *-*-* 07:00:00',
        ]);

        $this->assertSame(0, $result['exit_code']);
        $this->assertStringContains('Rendered verwaltung-weekly-audit-report.service and verwaltung-weekly-audit-report.timer', $result['stdout']);

        $servicePath = $outputDir . '/verwaltung-weekly-audit-report.service';
        $timerPath = $outputDir . '/verwaltung-weekly-audit-report.timer';
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

        @unlink($servicePath);
        @unlink($timerPath);
        @rmdir($outputDir);
    }

    public function testCronRendererProducesCronFileWithoutPlaceholders(): void
    {
        $outputPath = sys_get_temp_dir() . '/audit-cron-' . uniqid('', true);

        $result = $this->runScript([
            BASE_PATH . '/infra/scripts/render-weekly-audit-report-cron.sh',
            $outputPath,
            'root',
            'admin@verwaltung.local',
            '0 7 * * 1',
            '/var/log/verwaltung-weekly-audit-report.log',
        ]);

        $this->assertSame(0, $result['exit_code']);
        $this->assertStringContains('Rendered weekly audit report cron file', $result['stdout']);

        $cron = file_get_contents($outputPath);

        $this->assertTrue(is_string($cron) && $cron !== '');
        $this->assertStringContains('0 7 * * 1 root cd ' . BASE_PATH, $cron);
        $this->assertStringContains('/usr/bin/env bash ' . BASE_PATH . '/infra/scripts/send-weekly-audit-report.sh --admin-email=admin@verwaltung.local', $cron);
        $this->assertStringContains('/var/log/verwaltung-weekly-audit-report.log', $cron);
        $this->assertSame(false, str_contains($cron, '__CRON_SCHEDULE__'));

        @unlink($outputPath);
    }

    public function testRenderersFailWhenOutputArgumentIsMissing(): void
    {
        $systemd = $this->runScript([BASE_PATH . '/infra/scripts/render-weekly-audit-report-systemd.sh']);
        $cron = $this->runScript([BASE_PATH . '/infra/scripts/render-weekly-audit-report-cron.sh']);

        $this->assertSame(1, $systemd['exit_code']);
        $this->assertSame(1, $cron['exit_code']);
        $this->assertStringContains('Usage:', $systemd['stderr']);
        $this->assertStringContains('Usage:', $cron['stderr']);
    }

    private function runScript(array $command): array
    {
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
            throw new RuntimeException('Renderer script could not be started.');
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
}
