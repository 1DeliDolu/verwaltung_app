<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Services\FilesystemService;
use App\Models\InfrastructureService as InfrastructureServiceModel;

final class InfrastructureService
{
    public function __construct(private readonly ?App $app = null)
    {
    }

    public function all(): array
    {
        $services = InfrastructureServiceModel::all();

        foreach ($services as &$service) {
            $service['health'] = $this->healthForService($service);
        }
        unset($service);

        return $services;
    }

    public function departmentFileBrowser(DepartmentService $departmentService): array
    {
        $shares = [];
        $filesystem = new FilesystemService($departmentService->app());

        foreach ($departmentService->listVisibleDepartments() as $department) {
            $shares[] = [
                'department' => $department,
                'files' => $filesystem->listDepartmentFiles((string) $department['slug']),
            ];
        }

        return $shares;
    }

    private function healthForService(array $service): array
    {
        return match ((string) ($service['service_type'] ?? '')) {
            'mail' => $this->mailHealth(),
            'file' => $this->fileHealth(),
            default => [
                'state' => 'unknown',
                'label' => 'Unbekannt',
                'checks' => [],
            ],
        };
    }

    private function mailHealth(): array
    {
        $mailHost = $this->app?->config('mail.host', '127.0.0.1') ?? '127.0.0.1';
        $mailPort = (int) ($this->app?->config('mail.port', 1025) ?? 1025);
        $isDemo = (bool) ($this->app?->config('app.demo_mode', false) ?? false);

        $checks = [
            [
                'label' => 'SMTP',
                'ok' => $this->canConnect($mailHost, $mailPort),
            ],
        ];

        if ($isDemo) {
            $checks[] = [
                'label' => 'MailHog UI',
                'ok' => $this->canConnect('127.0.0.1', 8025),
            ];
        } else {
            $checks[] = [
                'label' => 'IMAP',
                'ok' => $this->canConnect('127.0.0.1', 143),
            ];
        }

        return $this->summarizeHealth($checks);
    }

    private function fileHealth(): array
    {
        $isDemo = (bool) ($this->app?->config('app.demo_mode', false) ?? false);
        $sambaPort = $isDemo ? 1445 : 445;
        $shareRoot = (string) ($this->app?->config('filesystems.disks.department_shares.root', BASE_PATH . '/infra/file/shares')
            ?? BASE_PATH . '/infra/file/shares');

        $checks = [
            [
                'label' => 'Samba Port',
                'ok' => $this->canConnect('127.0.0.1', $sambaPort),
            ],
            [
                'label' => 'Share Root',
                'ok' => is_dir($shareRoot),
            ],
        ];

        return $this->summarizeHealth($checks);
    }

    private function summarizeHealth(array $checks): array
    {
        $successfulChecks = count(array_filter($checks, static fn (array $check): bool => (bool) $check['ok']));
        $totalChecks = count($checks);

        if ($successfulChecks === $totalChecks) {
            return [
                'state' => 'healthy',
                'label' => 'Healthy',
                'checks' => $checks,
            ];
        }

        if ($successfulChecks === 0) {
            return [
                'state' => 'down',
                'label' => 'Down',
                'checks' => $checks,
            ];
        }

        return [
            'state' => 'degraded',
            'label' => 'Degraded',
            'checks' => $checks,
        ];
    }

    private function canConnect(string $host, int $port, float $timeout = 1.0): bool
    {
        $connection = @fsockopen($host, $port, $errorCode, $errorMessage, $timeout);

        if (!is_resource($connection)) {
            return false;
        }

        fclose($connection);

        return true;
    }
}
