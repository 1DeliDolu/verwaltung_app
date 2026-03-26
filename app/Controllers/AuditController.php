<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Services\AuditLogService;
use App\Services\AuditPresetService;
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

        $presetService = new AuditPresetService($this->app);
        $filters = $presetService->extractFilters([
            'source' => $request->input('source', ''),
            'search' => $request->input('search', ''),
            'outcome' => $request->input('outcome', ''),
            'date_from' => $request->input('date_from', ''),
            'date_to' => $request->input('date_to', ''),
        ]);

        $audit = new AuditLogService($this->app);
        $mergedEvents = $this->mergedEvents($audit, $filters);
        $summary = $this->summaries($audit, $filters);
        $trend = $this->dailyTrend($mergedEvents);
        $actionBreakdown = $this->actionBreakdown($mergedEvents);
        $topActors = $this->topActors($mergedEvents);
        $failureHeatmap = $this->failureHeatmap($mergedEvents);

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
            'trend' => $trend,
            'actionBreakdown' => $actionBreakdown,
            'topActors' => $topActors,
            'failureHeatmap' => $failureHeatmap,
            'filters' => $filters,
            'savedPresets' => $presetService->presetsForUser($currentUser),
            'savePresetAllowed' => $presetService->hasActiveFilters($filters),
            'currentAuditUrl' => $this->dashboardUrl($filters),
            'csrfToken' => CsrfMiddleware::token($this->app),
            'success' => $this->app->session()->consumeFlash('success'),
            'error' => $this->app->session()->consumeFlash('error'),
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

    public function storePreset(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $userService = new UserService($this->app);
        $currentUser = $userService->currentUser();

        if (!$userService->isAdmin($currentUser)) {
            $this->app->response()->render('errors/403', ['app' => $this->app], 'app', 403);
            return;
        }

        $presetService = new AuditPresetService($this->app);

        try {
            $presetService->savePreset($currentUser, [
                'name' => $request->input('name', ''),
                'source' => $request->input('source', ''),
                'search' => $request->input('search', ''),
                'outcome' => $request->input('outcome', ''),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
            ]);
            $this->app->session()->flash('success', 'Audit-Preset wurde gespeichert.');
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', $exception->getMessage());
        }

        $this->redirect($this->auditReturnPath((string) $request->input('return_to', '/audit')));
    }

    public function destroyPreset(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $userService = new UserService($this->app);
        $currentUser = $userService->currentUser();

        if (!$userService->isAdmin($currentUser)) {
            $this->app->response()->render('errors/403', ['app' => $this->app], 'app', 403);
            return;
        }

        $presetService = new AuditPresetService($this->app);

        try {
            $presetService->deletePreset($currentUser, (int) ($params['id'] ?? 0));
            $this->app->session()->flash('success', 'Audit-Preset wurde geloescht.');
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', $exception->getMessage());
        }

        $this->redirect($this->auditReturnPath((string) $request->input('return_to', '/audit')));
    }

    private function summaries(AuditLogService $audit, array $filters): array
    {
        return [
            'admin_user' => [
                'count' => count($audit->readAdminUserEvents($this->filtersForSource($filters, 'admin_user'))),
                'url' => $this->dashboardUrl(['source' => 'admin_user']),
            ],
            'task' => [
                'count' => count($audit->readTaskWorkflowEvents($this->filtersForSource($filters, 'task'))),
                'url' => $this->dashboardUrl(['source' => 'task']),
            ],
            'mail' => [
                'count' => count($audit->readMailActivityEvents($this->filtersForSource($filters, 'mail'))),
                'url' => $this->dashboardUrl(['source' => 'mail']),
            ],
            'calendar' => [
                'count' => count($audit->readCalendarActivityEvents($this->filtersForSource($filters, 'calendar'))),
                'url' => $this->dashboardUrl(['source' => 'calendar']),
            ],
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
                    'dashboard_url' => $this->dashboardUrl([
                        'source' => 'admin_user',
                        'search' => (string) ($event['target_user']['email'] ?? ''),
                    ]),
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
                    'dashboard_url' => $this->dashboardUrl([
                        'source' => 'task',
                        'search' => (string) ($event['task']['title'] ?? ''),
                    ]),
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
                    'dashboard_url' => $this->dashboardUrl([
                        'source' => 'mail',
                        'search' => (string) ($event['mail']['subject'] ?? ''),
                    ]),
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
                    'dashboard_url' => $this->dashboardUrl([
                        'source' => 'calendar',
                        'search' => (string) ($event['calendar_event']['title'] ?? ''),
                    ]),
                ];
            }
        }

        usort($events, static function (array $left, array $right): int {
            return strcmp((string) ($right['timestamp'] ?? ''), (string) ($left['timestamp'] ?? ''));
        });

        return array_slice($events, 0, 200);
    }

    private function dailyTrend(array $events): array
    {
        $buckets = [];

        foreach ($events as $event) {
            $date = substr((string) ($event['timestamp'] ?? ''), 0, 10);

            if ($date === '' || $date === false) {
                continue;
            }

            if (!isset($buckets[$date])) {
                $buckets[$date] = [
                    'date' => $date,
                    'total' => 0,
                    'success' => 0,
                    'failure' => 0,
                ];
            }

            $buckets[$date]['total']++;

            if ((string) ($event['outcome'] ?? '') === 'failure') {
                $buckets[$date]['failure']++;
            } else {
                $buckets[$date]['success']++;
            }
        }

        krsort($buckets);

        return array_slice(array_values($buckets), 0, 7);
    }

    private function actionBreakdown(array $events): array
    {
        $grouped = [];

        foreach ($events as $event) {
            $source = (string) ($event['source'] ?? 'unknown');
            $label = (string) ($event['source_label'] ?? $source);
            $action = (string) ($event['action'] ?? 'unknown');

            if (!isset($grouped[$source])) {
                $grouped[$source] = [
                    'label' => $label,
                    'total' => 0,
                    'actions' => [],
                ];
            }

            $grouped[$source]['total']++;
            $grouped[$source]['actions'][$action] = ($grouped[$source]['actions'][$action] ?? 0) + 1;
        }

        foreach ($grouped as $source => $data) {
            arsort($data['actions']);
            $grouped[$source]['actions'] = array_slice($data['actions'], 0, 5, true);
        }

        uasort($grouped, static fn (array $left, array $right): int => $right['total'] <=> $left['total']);

        return $grouped;
    }

    private function topActors(array $events): array
    {
        $actors = [];

        foreach ($events as $event) {
            $email = trim((string) ($event['actor_email'] ?? ''));

            if ($email === '') {
                continue;
            }

            if (!isset($actors[$email])) {
                $actors[$email] = [
                    'email' => $email,
                    'total' => 0,
                    'failure' => 0,
                    'sources' => [],
                ];
            }

            $actors[$email]['total']++;

            if ((string) ($event['outcome'] ?? '') === 'failure') {
                $actors[$email]['failure']++;
            }

            $source = (string) ($event['source_label'] ?? $event['source'] ?? 'Unknown');
            $actors[$email]['sources'][$source] = ($actors[$email]['sources'][$source] ?? 0) + 1;
        }

        uasort($actors, static function (array $left, array $right): int {
            $compare = $right['total'] <=> $left['total'];

            if ($compare !== 0) {
                return $compare;
            }

            return $right['failure'] <=> $left['failure'];
        });

        foreach ($actors as $email => $actor) {
            arsort($actor['sources']);
            $actors[$email]['sources'] = array_slice($actor['sources'], 0, 3, true);
        }

        return array_slice(array_values($actors), 0, 8);
    }

    private function failureHeatmap(array $events): array
    {
        $sources = [];

        foreach ($events as $event) {
            $source = (string) ($event['source'] ?? 'unknown');
            $label = (string) ($event['source_label'] ?? $source);

            if (!isset($sources[$source])) {
                $sources[$source] = [
                    'label' => $label,
                    'total' => 0,
                    'failure' => 0,
                ];
            }

            $sources[$source]['total']++;

            if ((string) ($event['outcome'] ?? '') === 'failure') {
                $sources[$source]['failure']++;
            }
        }

        foreach ($sources as $key => $source) {
            $sources[$key]['failure_rate'] = $source['total'] > 0
                ? (int) round(($source['failure'] / $source['total']) * 100)
                : 0;
        }

        uasort($sources, static function (array $left, array $right): int {
            $compare = $right['failure_rate'] <=> $left['failure_rate'];

            if ($compare !== 0) {
                return $compare;
            }

            return $right['failure'] <=> $left['failure'];
        });

        return $sources;
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

    private function dashboardUrl(array $params): string
    {
        $filtered = array_filter($params, static fn (mixed $value): bool => $value !== null && $value !== '');

        if ($filtered === []) {
            return '/audit';
        }

        return '/audit?' . http_build_query($filtered);
    }

    private function auditReturnPath(string $candidate): string
    {
        $candidate = trim($candidate);

        if ($candidate === '' || !str_starts_with($candidate, '/audit')) {
            return '/audit';
        }

        return $candidate;
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
