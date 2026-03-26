<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Models\User;
use DateTimeImmutable;
use RuntimeException;

final class AuditWeeklyReportCommandService
{
    public function __construct(
        private readonly App $app,
        private readonly ?AuditWeeklyReportService $reportService = null
    ) {
    }

    public function preview(?string $adminEmail = null, ?DateTimeImmutable $now = null, array $options = []): array
    {
        $admin = $this->resolveAdmin($adminEmail);
        $result = $this->report()->previewMeta($admin, $now, [
            'recipients' => $this->normalizeRecipients($options['recipients'] ?? []),
        ]);

        return [
            'admin_email' => (string) ($admin['email'] ?? ''),
            'recipients' => (array) ($result['recipients'] ?? []),
            'window' => (array) ($result['window'] ?? []),
            'subject' => 'Audit Wochenreport | ' . (string) (($result['window']['label'] ?? '')),
        ];
    }

    public function send(?string $adminEmail = null, ?DateTimeImmutable $now = null, array $options = []): array
    {
        $admin = $this->resolveAdmin($adminEmail);
        $recipients = $this->normalizeRecipients($options['recipients'] ?? []);
        $capturePath = trim((string) ($options['capture_path'] ?? ''));

        $result = $this->report()->sendWeeklyReport($admin, $now, [
            'recipients' => $recipients,
            'capture_path' => $capturePath,
        ]);

        return [
            'admin_email' => (string) ($admin['email'] ?? ''),
            ...$result,
        ];
    }

    private function report(): AuditWeeklyReportService
    {
        return $this->reportService ?? new AuditWeeklyReportService($this->app);
    }

    private function resolveAdmin(?string $adminEmail = null): array
    {
        $email = trim((string) ($adminEmail ?? ''));

        if ($email === '') {
            $email = trim((string) $this->app->config('mail.audit_report_admin_email', 'admin@verwaltung.local'));
        }

        if ($email === '') {
            throw new RuntimeException('Kein Admin fuer den Audit-Report konfiguriert.');
        }

        $user = User::findByEmail($email);

        if ($user === null) {
            throw new RuntimeException('Admin-Benutzer fuer den Audit-Report wurde nicht gefunden: ' . $email);
        }

        if ((string) ($user['role_name'] ?? '') !== 'admin') {
            throw new RuntimeException('Audit-Report CLI erfordert einen Admin-Benutzer: ' . $email);
        }

        return $user;
    }

    private function normalizeRecipients(array|string $recipients): array
    {
        $items = is_array($recipients) ? $recipients : [$recipients];
        $normalized = [];

        foreach ($items as $recipient) {
            foreach (explode(',', (string) $recipient) as $entry) {
                $trimmed = trim($entry);

                if ($trimmed !== '') {
                    $normalized[] = $trimmed;
                }
            }
        }

        return array_values(array_unique($normalized));
    }
}
