<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Services\AuditLogService;
use App\Services\DepartmentService;
use App\Services\FilesystemService;
use App\Services\TaskService;

final class DepartmentController extends Controller
{
    public function index(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new DepartmentService($this->app);

        $this->render('departments/index', [
            'app' => $this->app,
            'departments' => $service->listVisibleDepartmentsWithSummaryStats(),
            'user' => $service->currentUser(),
        ]);
    }

    public function show(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new DepartmentService($this->app);
        $department = $service->findVisibleDepartmentWithSummaryStats((string) ($params['slug'] ?? ''));

        if ($department === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        $taskService = new TaskService($this->app);
        $user = $service->currentUser();

        $this->render('departments/show', [
            'app' => $this->app,
            'user' => $user,
            'department' => $department,
            'documents' => $service->documentsForDepartment((int) $department['id']),
            'employees' => $service->employeesForDepartment($department),
            'isHumanResourcesDepartment' => $service->isHumanResourcesDepartment($department),
            'isInformationTechnologyDepartment' => $service->isInformationTechnologyDepartment($department),
            'assignableDepartments' => $service->assignableDepartments($department),
            'eligiblePersonnelUsers' => $service->eligiblePersonnelUsers($department),
            'shareFiles' => (new FilesystemService($this->app))->listDepartmentFiles((string) $department['slug']),
            'canManage' => $service->mayManageDepartment($department),
            'departmentTasks' => $taskService->recentTasks($user, ['department_id' => (int) $department['id']], 4),
            'departmentTaskStatusCounts' => $taskService->statusCounts($user, ['department_id' => (int) $department['id']]),
            'taskStatuses' => TaskService::statuses(),
            'taskPriorities' => TaskService::priorities(),
            'csrfToken' => CsrfMiddleware::token($this->app),
            'success' => $this->app->session()->consumeFlash('success'),
            'error' => $this->app->session()->consumeFlash('error'),
        ]);
    }

    public function storeDocument(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new DepartmentService($this->app);
        $audit = new AuditLogService($this->app);
        $department = $service->findVisibleDepartment((string) ($params['slug'] ?? ''));

        if ($department === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        try {
            $service->createDocument($department, [
                'folder_name' => (string) $request->input('folder_name', ''),
                'title' => (string) $request->input('title', ''),
                'body' => (string) $request->input('body', ''),
            ]);
            $this->app->session()->flash('success', 'Dokument wurde erstellt.');
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', 'Dokument konnte nicht gespeichert werden.');
        }

        $this->redirect('/departments/' . $department['slug']);
    }

    public function storeManagedPerson(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new DepartmentService($this->app);
        $department = $service->findVisibleDepartment((string) ($params['slug'] ?? ''));

        if ($department === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        try {
            $service->createManagedPerson($department, [
                'name' => (string) $request->input('name', ''),
                'email' => (string) $request->input('email', ''),
                'temporary_password' => (string) $request->input('temporary_password', ''),
                'temporary_password_confirmation' => (string) $request->input('temporary_password_confirmation', ''),
                'target_department_id' => (int) $request->input('target_department_id', 0),
                'membership_role' => (string) $request->input('membership_role', 'employee'),
            ]);
            $this->app->session()->flash('success', 'Person wurde von IT angelegt. Passwortwechsel ist beim ersten Login verpflichtend.');
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', 'Person konnte nicht angelegt werden.');
        }

        $this->redirect('/departments/' . $department['slug']);
    }

    public function storeEmployee(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new DepartmentService($this->app);
        $department = $service->findVisibleDepartment((string) ($params['slug'] ?? ''));

        if ($department === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        try {
            $service->createEmployee($department, [
                'user_id' => (int) $request->input('user_id', 0),
                'position_title' => (string) $request->input('position_title', ''),
                'employment_status' => (string) $request->input('employment_status', 'active'),
                'hired_at' => (string) $request->input('hired_at', ''),
                'personnel_rights' => (string) $request->input('personnel_rights', ''),
                'notes' => (string) $request->input('notes', ''),
                'data_processing_basis' => (string) $request->input('data_processing_basis', ''),
                'retention_until' => (string) $request->input('retention_until', ''),
            ]);
            $this->app->session()->flash('success', 'Personalprofil wurde angelegt. Die Personalnummer wurde automatisch vergeben.');
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', 'Personalprofil konnte nicht angelegt werden.');
        }

        $this->redirect('/departments/' . $department['slug']);
    }

    public function uploadEmployeeDocument(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new DepartmentService($this->app);
        $audit = new AuditLogService($this->app);
        $department = $service->findVisibleDepartment((string) ($params['slug'] ?? ''));

        if ($department === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        try {
            $employeeId = (int) $request->input('employee_id', 0);
            $file = $request->file('employee_document');

            if ($employeeId <= 0 || $file === null) {
                throw new \RuntimeException('Missing employee document upload payload.');
            }

            $document = $service->createEmployeeDocument($department, $employeeId, $file);
            $audit->recordPersonnelDocumentEvent('upload', [
                'actor' => $service->currentUser(),
                'department' => $department,
                'employee' => ['id' => $employeeId],
                'document' => $document,
            ]);
            $this->app->session()->flash('success', 'Mitarbeiterdokument wurde gespeichert.');
        } catch (\RuntimeException $exception) {
            $audit->recordPersonnelDocumentEvent('upload', [
                'actor' => $this->auditActor($service),
                'department' => $department,
                'employee' => ['id' => (int) $request->input('employee_id', 0)],
                'outcome' => 'failure',
                'reason' => $exception->getMessage(),
            ]);
            $this->app->session()->flash('error', 'Mitarbeiterdokument konnte nicht gespeichert werden.');
        }

        $this->redirect('/departments/' . $department['slug']);
    }

    public function updateEmployee(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new DepartmentService($this->app);
        $department = $service->findVisibleDepartment((string) ($params['slug'] ?? ''));

        if ($department === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        try {
            $service->updateEmployee($department, (int) ($params['employeeId'] ?? 0), [
                'position_title' => (string) $request->input('position_title', ''),
                'employment_status' => (string) $request->input('employment_status', 'active'),
                'hired_at' => (string) $request->input('hired_at', ''),
                'personnel_rights' => (string) $request->input('personnel_rights', ''),
                'notes' => (string) $request->input('notes', ''),
                'data_processing_basis' => (string) $request->input('data_processing_basis', ''),
                'retention_until' => (string) $request->input('retention_until', ''),
            ]);
            $this->app->session()->flash('success', 'Personalprofil wurde aktualisiert.');
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', 'Personalprofil konnte nicht aktualisiert werden.');
        }

        $this->redirect('/departments/' . $department['slug']);
    }

    public function destroyEmployee(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new DepartmentService($this->app);
        $department = $service->findVisibleDepartment((string) ($params['slug'] ?? ''));

        if ($department === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        try {
            $service->deleteEmployee($department, (int) ($params['employeeId'] ?? 0));
            $this->app->session()->flash('success', 'Mitarbeiter und Personalakten wurden entfernt.');
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', 'Mitarbeiter konnte nicht geloescht werden.');
        }

        $this->redirect('/departments/' . $department['slug']);
    }

    public function destroyEmployeeDocument(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new DepartmentService($this->app);
        $audit = new AuditLogService($this->app);
        $department = $service->findVisibleDepartment((string) ($params['slug'] ?? ''));

        if ($department === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        try {
            $document = $service->deleteEmployeeDocument(
                $department,
                (int) ($params['employeeId'] ?? 0),
                (int) ($params['documentId'] ?? 0)
            );
            $audit->recordPersonnelDocumentEvent('delete', [
                'actor' => $service->currentUser(),
                'department' => $department,
                'employee' => ['id' => (int) ($params['employeeId'] ?? 0)],
                'document' => $document,
            ]);
            $this->app->session()->flash('success', 'Personalakte wurde entfernt.');
        } catch (\RuntimeException $exception) {
            $audit->recordPersonnelDocumentEvent('delete', [
                'actor' => $this->auditActor($service),
                'department' => $department,
                'employee' => ['id' => (int) ($params['employeeId'] ?? 0)],
                'document' => ['id' => (int) ($params['documentId'] ?? 0)],
                'outcome' => 'failure',
                'reason' => $exception->getMessage(),
            ]);
            $this->app->session()->flash('error', 'Personalakte konnte nicht geloescht werden.');
        }

        $this->redirect('/departments/' . $department['slug']);
    }

    public function uploadFile(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new DepartmentService($this->app);
        $audit = new AuditLogService($this->app);
        $department = $service->findVisibleDepartment((string) ($params['slug'] ?? ''));

        if ($department === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        try {
            if (!$service->mayManageDepartment($department)) {
                throw new \RuntimeException('Not allowed to upload.');
            }

            $file = $request->file('upload_file');

            if ($file === null) {
                throw new \RuntimeException('No file uploaded.');
            }

            (new FilesystemService($this->app))->storeDepartmentUpload((string) $department['slug'], $file);
            $this->app->session()->flash('success', 'Datei wurde in den Abteilungsordner hochgeladen.');
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', 'Datei konnte nicht hochgeladen werden.');
        }

        $this->redirect('/departments/' . $department['slug']);
    }

    public function downloadEmployeeDocument(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new DepartmentService($this->app);
        $audit = new AuditLogService($this->app);
        $department = $service->findVisibleDepartment((string) ($params['slug'] ?? ''));

        if ($department === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        $employeeId = (int) ($params['employeeId'] ?? 0);
        $documentId = (int) ($params['documentId'] ?? 0);
        $document = $service->employeeDocumentForDownload($department, $employeeId, $documentId);

        if ($document === null) {
            $audit->recordPersonnelDocumentEvent('download', [
                'actor' => $this->auditActor($service),
                'department' => $department,
                'employee' => ['id' => $employeeId],
                'document' => ['id' => $documentId],
                'outcome' => 'failure',
                'reason' => 'Employee document could not be found or is not accessible.',
            ]);
            http_response_code(404);
            echo 'Employee document not found.';
            return;
        }

        try {
            $content = (new FilesystemService($this->app))->readDepartmentFile(
                (string) $department['slug'],
                (string) $document['file_path']
            );
        } catch (\RuntimeException $exception) {
            $audit->recordPersonnelDocumentEvent('download', [
                'actor' => $this->auditActor($service),
                'department' => $department,
                'employee' => ['id' => $employeeId],
                'document' => $document,
                'outcome' => 'failure',
                'reason' => $exception->getMessage(),
            ]);
            http_response_code(404);
            echo 'Employee document not found.';
            return;
        }

        $audit->recordPersonnelDocumentEvent('download', [
            'actor' => $service->currentUser(),
            'department' => $department,
            'employee' => ['id' => $employeeId],
            'document' => $document,
        ]);

        header('Content-Description: File Transfer');
        header('Content-Type: ' . ((string) ($document['mime_type'] ?? '') !== '' ? $document['mime_type'] : 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . addslashes((string) $document['original_name']) . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }

    private function auditActor(DepartmentService $service): ?array
    {
        try {
            return $service->currentUser();
        } catch (\RuntimeException $exception) {
            return null;
        }
    }

    public function openDepartmentFile(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new DepartmentService($this->app);
        $department = $service->findVisibleDepartment((string) ($params['slug'] ?? ''));

        if ($department === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        $relativePath = trim((string) $request->input('path', ''));

        if ($relativePath === '') {
            http_response_code(404);
            echo 'Department file not found.';
            return;
        }

        try {
            $filesystem = new FilesystemService($this->app);
            $content = $filesystem->readDepartmentFile((string) $department['slug'], $relativePath);
            $metadata = $filesystem->departmentFileMetadata((string) $department['slug'], $relativePath);
        } catch (\RuntimeException $exception) {
            http_response_code(404);
            echo 'Department file not found.';
            return;
        }

        header('Content-Type: ' . $metadata['mime_type']);
        header('Content-Disposition: inline; filename="' . addslashes((string) $metadata['name']) . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }
}
