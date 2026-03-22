<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\VerifiedMiddleware;
use App\Services\DepartmentService;
use App\Services\FilesystemService;

final class DepartmentController extends Controller
{
    public function index(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        VerifiedMiddleware::handle($this->app);

        $service = new DepartmentService($this->app);

        $this->render('departments/index', [
            'app' => $this->app,
            'departments' => $service->listVisibleDepartments(),
            'user' => $service->currentUser(),
        ]);
    }

    public function show(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        VerifiedMiddleware::handle($this->app);

        $service = new DepartmentService($this->app);
        $department = $service->findVisibleDepartment((string) ($params['slug'] ?? ''));

        if ($department === null) {
            $this->app->response()->render('errors/404', ['app' => $this->app], 'app', 404);
            return;
        }

        $this->render('departments/show', [
            'app' => $this->app,
            'user' => $service->currentUser(),
            'department' => $department,
            'documents' => $service->documentsForDepartment((int) $department['id']),
            'shareFiles' => (new FilesystemService($this->app))->listDepartmentFiles((string) $department['slug']),
            'canManage' => $service->mayManageDepartment($department),
            'csrfToken' => CsrfMiddleware::token($this->app),
            'success' => $this->app->session()->consumeFlash('success'),
            'error' => $this->app->session()->consumeFlash('error'),
        ]);
    }

    public function storeDocument(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        VerifiedMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new DepartmentService($this->app);
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

    public function uploadFile(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        VerifiedMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new DepartmentService($this->app);
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
}
