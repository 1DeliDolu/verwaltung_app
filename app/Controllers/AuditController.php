<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Services\AuditDashboardService;
use App\Services\AuditPresetService;
use App\Services\AuditWeeklyReportService;
use App\Services\UserService;

final class AuditController extends Controller
{
    public function index(Request $request, array $params = []): void
    {
        $currentUser = $this->requireAdminUser();

        if ($currentUser === null) {
            return;
        }

        $presetService = new AuditPresetService($this->app);
        $dashboardService = new AuditDashboardService($this->app);
        $weeklyReportService = new AuditWeeklyReportService($this->app, $dashboardService);
        $filters = $presetService->extractFilters([
            'source' => $request->input('source', ''),
            'search' => $request->input('search', ''),
            'outcome' => $request->input('outcome', ''),
            'date_from' => $request->input('date_from', ''),
            'date_to' => $request->input('date_to', ''),
        ]);
        $dashboard = $dashboardService->build($filters);

        if ((string) $request->input('format', '') === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="audit-dashboard.csv"');
            echo $dashboardService->eventsAsCsv($dashboard['events']);
            return;
        }

        $this->render('audit/index', [
            'app' => $this->app,
            'user' => $currentUser,
            'filters' => $filters,
            'savedPresets' => $presetService->presetsForUser($currentUser),
            'savePresetAllowed' => $presetService->hasActiveFilters($filters),
            'currentAuditUrl' => $dashboardService->dashboardUrl($filters),
            'csrfToken' => CsrfMiddleware::token($this->app),
            'success' => $this->app->session()->consumeFlash('success'),
            'error' => $this->app->session()->consumeFlash('error'),
            'sourceOptions' => $dashboardService->sourceOptions(),
            'outcomeOptions' => $dashboardService->outcomeOptions(),
            'weeklyReportMeta' => $weeklyReportService->previewMeta($currentUser),
            ...$dashboard,
        ]);
    }

    public function storePreset(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $currentUser = $this->requireAdminUser(false);

        if ($currentUser === null) {
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

        $currentUser = $this->requireAdminUser(false);

        if ($currentUser === null) {
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

    public function sendWeeklyReport(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $currentUser = $this->requireAdminUser(false);

        if ($currentUser === null) {
            return;
        }

        $reportService = new AuditWeeklyReportService($this->app);

        try {
            $result = $reportService->sendWeeklyReport($currentUser);
            $this->app->session()->flash(
                'success',
                sprintf(
                    'Audit-Wochenreport wurde an %d Empfaenger gesendet.',
                    count((array) ($result['recipients'] ?? []))
                )
            );
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', $exception->getMessage());
        }

        $this->redirect($this->auditReturnPath((string) $request->input('return_to', '/audit')));
    }

    private function requireAdminUser(bool $handleAuth = true): ?array
    {
        if ($handleAuth) {
            AuthMiddleware::handle($this->app);
        }

        $userService = new UserService($this->app);
        $currentUser = $userService->currentUser();

        if (!$userService->isAdmin($currentUser)) {
            $this->app->response()->render('errors/403', [
                'app' => $this->app,
            ], 'app', 403);

            return null;
        }

        return $currentUser;
    }

    private function auditReturnPath(string $candidate): string
    {
        $candidate = trim($candidate);

        if ($candidate === '' || !str_starts_with($candidate, '/audit')) {
            return '/audit';
        }

        return $candidate;
    }
}
