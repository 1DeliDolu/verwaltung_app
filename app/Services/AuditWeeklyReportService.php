<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Core\View;
use DateTimeImmutable;
use RuntimeException;
use Throwable;

final class AuditWeeklyReportService
{
    public function __construct(
        private readonly App $app,
        private readonly ?AuditDashboardService $dashboardService = null
    ) {
    }

    public function previewMeta(array $user, ?DateTimeImmutable $now = null, array $options = []): array
    {
        $this->assertAdmin($user);

        return [
            'window' => $this->dashboard()->weeklyWindow($this->resolvedNow($now)),
            'recipients' => $this->resolveRecipients($user, $options),
        ];
    }

    public function sendWeeklyReport(array $user, ?DateTimeImmutable $now = null, array $options = []): array
    {
        $this->assertAdmin($user);

        $resolvedNow = $this->resolvedNow($now);
        $window = $this->dashboard()->weeklyWindow($resolvedNow);
        $recipients = $this->resolveRecipients($user, $options);

        if ($recipients === []) {
            throw new RuntimeException('Kein Empfaenger fuer den Audit-Wochenreport konfiguriert.');
        }

        $dashboard = $this->dashboard()->build([
            'source' => '',
            'search' => '',
            'outcome' => '',
            'date_from' => $window['date_from'],
            'date_to' => $window['date_to'],
        ]);

        $stats = $this->dashboard()->overallStats($dashboard['events']);
        $topActors = array_slice($dashboard['topActors'], 0, 5);
        $recentFailures = $this->dashboard()->recentFailures($dashboard['events']);
        $sourceSummary = [];

        foreach ($dashboard['summary'] as $sourceKey => $source) {
            $sourceSummary[] = [
                'key' => $sourceKey,
                'label' => (string) ($source['label'] ?? $sourceKey),
                'count' => (int) ($source['count'] ?? 0),
            ];
        }

        $csvName = sprintf(
            'audit-weekly-report-%s-to-%s.csv',
            (string) $window['date_from'],
            (string) $window['date_to']
        );
        $subject = 'Audit Wochenreport | ' . (string) $window['label'];
        $templateData = [
            'user' => $user,
            'recipients' => $recipients,
            'window' => $window,
            'subject' => $subject,
            'generated_at' => $resolvedNow->format('Y-m-d H:i:s'),
            'stats' => $stats,
            'source_summary' => $sourceSummary,
            'top_actors' => $topActors,
            'recent_failures' => $recentFailures,
            'csv_name' => $csvName,
        ];

        $textBody = View::render('mail/templates/audit-weekly-report-text', $templateData, 'plain');
        $htmlBody = View::render('mail/templates/audit-weekly-report-html', $templateData, 'plain');

        (new MailService($this->app))->sendMessage(
            $recipients,
            $subject,
            $textBody,
            null,
            null,
            [
                'template' => 'audit-weekly-report',
                'html_body' => $htmlBody,
                'capture_path' => $options['capture_path'] ?? null,
                'attachments' => [[
                    'name' => $csvName,
                    'mime' => 'text/csv; charset=UTF-8',
                    'content' => $this->dashboard()->eventsAsCsv($dashboard['events']),
                ]],
            ]
        );

        return [
            'subject' => $subject,
            'recipients' => $recipients,
            'window' => $window,
            'stats' => $stats,
            'csv_name' => $csvName,
        ];
    }

    private function dashboard(): AuditDashboardService
    {
        return $this->dashboardService ?? new AuditDashboardService($this->app);
    }

    private function resolvedNow(?DateTimeImmutable $now = null): DateTimeImmutable
    {
        if ($now instanceof DateTimeImmutable) {
            return $now;
        }

        $configuredNow = trim((string) $this->app->config('mail.audit_report_now', ''));

        if ($configuredNow !== '') {
            try {
                return new DateTimeImmutable($configuredNow);
            } catch (Throwable) {
            }
        }

        return new DateTimeImmutable('now');
    }

    private function resolveRecipients(array $user, array $options = []): array
    {
        $recipients = [];

        foreach ((array) ($options['recipients'] ?? $this->app->config('mail.audit_report_recipients', [])) as $recipient) {
            $trimmed = trim((string) $recipient);

            if ($trimmed !== '') {
                $recipients[] = $trimmed;
            }
        }

        if ($recipients === []) {
            $fallbackUserEmail = trim((string) ($user['email'] ?? ''));

            if ($fallbackUserEmail !== '') {
                $recipients[] = $fallbackUserEmail;
            }
        }

        if ($recipients === []) {
            $fallbackRecipient = trim((string) $this->app->config('mail.test_recipient', ''));

            if ($fallbackRecipient !== '') {
                $recipients[] = $fallbackRecipient;
            }
        }

        return array_values(array_unique($recipients));
    }

    private function assertAdmin(array $user): void
    {
        if ((string) ($user['role_name'] ?? '') !== 'admin') {
            throw new RuntimeException('Nur Admins duerfen Audit-Wochenreports senden.');
        }
    }
}
