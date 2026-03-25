<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Services\AuditLogService;
use App\Services\UserService;

final class AuditController extends Controller
{
    public function index(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $userService = new UserService($this->app);
        $currentUser = $userService->currentUser();

        if (!$userService->isAdmin($currentUser)) {
            $this->app->response()->render('errors/403', [
                'app' => $this->app,
            ], 'app', 403);
            return;
        }

        $filters = [
            'source' => trim((string) $request->input('source', '')),
            'search' => trim((string) $request->input('search', '')),
            'outcome' => trim((string) $request->input('outcome', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        $audit = new AuditLogService($this->app);
        $mergedEvents = $this->mergedEvents($audit, $filters);
        $summary = $this->summaries($audit, $filters);

        if ((string) $request->input('format', '') === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="audit-dashboard.csv"');
            echo $this->eventsAsCsv($mergedEvents);
            return;
        }

        $this->render('audit/index', [
            'app' => $this->app,
            'user' => $currentUser,
            'events' => $mergedEvents,
            'summary' => $summary,
            'filters' => $filters,
            'sourceOptions' => [
                'admin_user' => 'User Management',
                'task' => 'Tasks',
                'mail' => 'Mail',
                'calendar' => 'Calendar',
            ],
            'outcomeOptions' => [
                'success' => 'Erfolg',
                'failure' => 'Fehler',
            ],
        ]);
    }

    private function summaries(AuditLogService $audit, array $filters): array
    {
        return [
            'admin_user' => count($audit->readAdminUserEvents($this->filtersForSource($filters, 'admin_user'))),
            'task' => count($audit->readTaskWorkflowEvents($this->filtersForSource($filters, 'task'))),
            'mail' => count($audit->readMailActivityEvents($this->filtersForSource($filters, 'mail'))),
            'calendar' => count($audit->readCalendarActivityEvents($this->filtersForSource($filters, 'calendar'))),
        ];
    }

    private function mergedEvents(AuditLogService $audit, array $filters): array
    {
        $events = [];
        $source = (string) ($filters['source'] ?? '');

        if ($source === '' || $source === 'admin_user') {
            foreach ($audit->readAdminUserEvents($this->filtersForSource($filters, 'admin_user')) as $event) {
                $events[] = [
                    'source' => 'admin_user',
                    'source_label' => 'User Management',
                    'timestamp' => (string) ($event['timestamp'] ?? ''),
                    'action' => (string) ($event['action'] ?? ''),
                    'outcome' => (string) ($event['outcome'] ?? ''),
                    'actor_email' => (string) ($event['actor']['email'] ?? ''),
                    'subject' => (string) ($event['target_user']['email'] ?? ''),
                    'context' => (string) ($event['department']['name'] ?? $event['department']['slug'] ?? ''),
                    'reason' => (string) ($event['reason'] ?? ''),
                    'detail_url' => '/users/audit',
                ];
            }
        }

        if ($source === '' || $source === 'task') {
            foreach ($audit->readTaskWorkflowEvents($this->filtersForSource($filters, 'task')) as $event) {
                $events[] = [
                    'source' => 'task',
                    'source_label' => 'Tasks',
                    'timestamp' => (string) ($event['timestamp'] ?? ''),
                    'action' => (string) ($event['action'] ?? ''),
                    'outcome' => (string) ($event['outcome'] ?? ''),
                    'actor_email' => (string) ($event['actor']['email'] ?? ''),
                    'subject' => (string) ($event['task']['title'] ?? ''),
                    'context' => (string) ($event['department']['name'] ?? $event['department']['slug'] ?? ''),
                    'reason' => (string) ($event['reason'] ?? ''),
                    'detail_url' => '/tasks/audit',
                ];
            }
        }

        if ($source === '' || $source === 'mail') {
            foreach ($audit->readMailActivityEvents($this->filtersForSource($filters, 'mail')) as $event) {
                $events[] = [
                    'source' => 'mail',
                    'source_label' => 'Mail',
                    'timestamp' => (string) ($event['timestamp'] ?? ''),
                    'action' => (string) ($event['action'] ?? ''),
                    'outcome' => (string) ($event['outcome'] ?? ''),
                    'actor_email' => (string) ($event['actor']['email'] ?? ''),
                    'subject' => (string) ($event['mail']['subject'] ?? ''),
                    'context' => (string) ($event['metadata']['folder'] ?? ''),
                    'reason' => (string) ($event['reason'] ?? ''),
                    'detail_url' => '/mail/audit',
                ];
            }
        }

        if ($source === '' || $source === 'calendar') {
            foreach ($audit->readCalendarActivityEvents($this->filtersForSource($filters, 'calendar')) as $event) {
                $events[] = [
                    'source' => 'calendar',
                    'source_label' => 'Calendar',
                    'timestamp' => (string) ($event['timestamp'] ?? ''),
                    'action' => (string) ($event['action'] ?? ''),
                    'outcome' => (string) ($event['outcome'] ?? ''),
                    'actor_email' => (string) ($event['actor']['email'] ?? ''),
                    'subject' => (string) ($event['calendar_event']['title'] ?? ''),
                    'context' => implode(', ', (array) ($event['calendar_event']['department_names'] ?? [])),
                    'reason' => (string) ($event['reason'] ?? ''),
                    'detail_url' => '/calendar/audit',
                ];
            }
        }

        usort($events, static function (array $left, array $right): int {
            return strcmp((string) ($right['timestamp'] ?? ''), (string) ($left['timestamp'] ?? ''));
        });

        return array_slice($events, 0, 200);
    }

    private function filtersForSource(array $filters, string $source): array
    {
        return [
            'search' => $filters['search'] ?? '',
            'outcome' => $filters['outcome'] ?? '',
            'date_from' => $filters['date_from'] ?? '',
            'date_to' => $filters['date_to'] ?? '',
        ];
    }

    private function eventsAsCsv(array $events): string
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            return '';
        }

        fputcsv($stream, [
            'timestamp',
            'source',
            'action',
            'outcome',
            'actor_email',
            'subject',
            'context',
            'reason',
            'detail_url',
        ], ',', '"', '\\');

        foreach ($events as $event) {
            fputcsv($stream, [
                (string) ($event['timestamp'] ?? ''),
                (string) ($event['source'] ?? ''),
                (string) ($event['action'] ?? ''),
                (string) ($event['outcome'] ?? ''),
                (string) ($event['actor_email'] ?? ''),
                (string) ($event['subject'] ?? ''),
                (string) ($event['context'] ?? ''),
                (string) ($event['reason'] ?? ''),
                (string) ($event['detail_url'] ?? ''),
            ], ',', '"', '\\');
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return is_string($csv) ? $csv : '';
    }
}
