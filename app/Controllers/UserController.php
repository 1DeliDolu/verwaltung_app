<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Services\AuditLogService;
use App\Services\UserService;

final class UserController extends Controller
{
    public function index(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new UserService($this->app);
        $currentUser = $service->currentUser();

        if (!$service->isAdmin($currentUser)) {
            $this->app->response()->render('errors/403', [
                'app' => $this->app,
            ], 'app', 403);
            return;
        }

        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'department' => trim((string) $request->input('department', '')),
            'membership_role' => trim((string) $request->input('membership_role', '')),
        ];

        $this->render('users/index', [
            'app' => $this->app,
            'user' => $currentUser,
            'leaders' => $service->departmentLeaderDirectory($filters),
            'departments' => $service->assignableDepartments(),
            'membershipRoles' => $service->membershipRoleOptions(),
            'filters' => $filters,
            'defaultLeaderPassword' => UserService::DEFAULT_DEPARTMENT_LEADER_PASSWORD,
            'csrfToken' => CsrfMiddleware::token($this->app),
            'success' => $this->app->session()->consumeFlash('success'),
            'error' => $this->app->session()->consumeFlash('error'),
        ]);
    }

    public function audit(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new UserService($this->app);
        $currentUser = $service->currentUser();

        if (!$service->isAdmin($currentUser)) {
            $this->app->response()->render('errors/403', [
                'app' => $this->app,
            ], 'app', 403);
            return;
        }

        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'action' => trim((string) $request->input('action', '')),
            'outcome' => trim((string) $request->input('outcome', '')),
        ];

        $this->render('users/audit', [
            'app' => $this->app,
            'user' => $currentUser,
            'events' => (new AuditLogService($this->app))->readAdminUserEvents($filters),
            'filters' => $filters,
            'actionOptions' => [
                'reset_password' => 'Passwort Reset',
                'update_assignment' => 'Zuordnung',
            ],
            'outcomeOptions' => [
                'success' => 'Erfolg',
                'failure' => 'Fehler',
            ],
        ]);
    }

    public function resetPassword(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new UserService($this->app);
        $audit = new AuditLogService($this->app);
        $currentUser = $service->currentUser();

        if (!$service->isAdmin($currentUser)) {
            $this->app->response()->render('errors/403', [
                'app' => $this->app,
            ], 'app', 403);
            return;
        }

        try {
            $targetUserId = (int) ($params['id'] ?? 0);
            $targetUser = $service->findManagedLeader($targetUserId);

            $service->resetDepartmentLeaderPassword($currentUser, $targetUserId);
            $audit->recordAdminUserEvent('reset_password', [
                'actor' => $currentUser,
                'target_user' => $targetUser,
                'metadata' => [
                    'target_email' => (string) ($targetUser['email'] ?? ''),
                    'reset_to_default_password' => true,
                ],
            ]);
            $this->app->session()->flash('success', 'Leiter-Passwort wurde zurueckgesetzt und muss beim naechsten Login geaendert werden.');
        } catch (\RuntimeException $exception) {
            $audit->recordAdminUserEvent('reset_password', [
                'actor' => $currentUser,
                'target_user' => ['id' => (int) ($params['id'] ?? 0)],
                'outcome' => 'failure',
                'reason' => $exception->getMessage(),
            ]);
            $this->app->session()->flash('error', 'Leiter-Passwort konnte nicht zurueckgesetzt werden.');
        }

        $this->redirect('/users');
    }

    public function updateAssignment(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new UserService($this->app);
        $audit = new AuditLogService($this->app);
        $currentUser = $service->currentUser();

        if (!$service->isAdmin($currentUser)) {
            $this->app->response()->render('errors/403', [
                'app' => $this->app,
            ], 'app', 403);
            return;
        }

        try {
            $targetUserId = (int) ($params['id'] ?? 0);
            $departmentId = (int) $request->input('department_id', 0);
            $membershipRole = (string) $request->input('membership_role', '');
            $targetUser = $service->findManagedLeader($targetUserId);
            $targetDepartment = $service->findAssignableDepartment($departmentId);

            $service->updateDepartmentLeaderAssignment(
                $currentUser,
                $targetUserId,
                $departmentId,
                $membershipRole
            );
            $audit->recordAdminUserEvent('update_assignment', [
                'actor' => $currentUser,
                'target_user' => $targetUser,
                'department' => $targetDepartment,
                'metadata' => [
                    'membership_role' => $membershipRole,
                    'target_email' => (string) ($targetUser['email'] ?? ''),
                ],
            ]);
            $this->app->session()->flash('success', 'Leiter-Zuordnung wurde aktualisiert.');
        } catch (\RuntimeException $exception) {
            $audit->recordAdminUserEvent('update_assignment', [
                'actor' => $currentUser,
                'target_user' => ['id' => (int) ($params['id'] ?? 0)],
                'department' => ['id' => (int) $request->input('department_id', 0)],
                'metadata' => [
                    'membership_role' => (string) $request->input('membership_role', ''),
                ],
                'outcome' => 'failure',
                'reason' => $exception->getMessage(),
            ]);
            $this->app->session()->flash('error', 'Leiter-Zuordnung konnte nicht aktualisiert werden.');
        }

        $this->redirect('/users');
    }
}
