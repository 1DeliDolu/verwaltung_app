<?php

use App\Controllers\AuthController;
use App\Controllers\CalendarController;
use App\Controllers\DashboardController;
use App\Controllers\DepartmentController;
use App\Controllers\InfrastructureController;
use App\Controllers\MailController;
use App\Controllers\InternalMailController;
use App\Controllers\PageController;
use App\Controllers\TaskController;
use App\Controllers\UserController;
use App\Controllers\VerificationController;

$router = $app->router();

$router->get('/', [PageController::class, 'news']);
$router->get('/news', [PageController::class, 'news']);
$router->get('/dashboard', [DashboardController::class, 'index']);

$router->group('/calendar', function ($router): void {
    $router->get('/', [CalendarController::class, 'index']);
    $router->get('/audit', [CalendarController::class, 'audit']);

    $router->group('/events', function ($router): void {
        $router->post('/', [CalendarController::class, 'store']);
        $router->post('/{id}/update', [CalendarController::class, 'update']);
        $router->post('/{id}/delete', [CalendarController::class, 'destroy']);
        $router->post('/{id}/complete', [CalendarController::class, 'complete']);
    });
});

$router->group('/tasks', function ($router): void {
    $router->get('/', [TaskController::class, 'index']);
    $router->get('/audit', [TaskController::class, 'audit']);
    $router->get('/create', [TaskController::class, 'create']);
    $router->post('/', [TaskController::class, 'store']);
    $router->get('/{id}', [TaskController::class, 'show']);
    $router->get('/{id}/edit', [TaskController::class, 'edit']);
    $router->post('/{id}/update', [TaskController::class, 'update']);
    $router->post('/{id}/status', [TaskController::class, 'updateStatus']);
    $router->post('/{id}/comments', [TaskController::class, 'addComment']);
});

$router->group('/email', function ($router): void {
    $router->get('/verify', [VerificationController::class, 'notice']);
    $router->post('/verification-notification', [VerificationController::class, 'resend']);
    $router->get('/verify/{id}/{token}', [VerificationController::class, 'verify']);
});

$router->group('/mail', function ($router): void {
    $router->get('/', [InternalMailController::class, 'index']);
    $router->get('/audit', [InternalMailController::class, 'audit']);
    $router->post('/send', [InternalMailController::class, 'send']);
    $router->post('/demo-send', [MailController::class, 'sendDemo']);
    $router->get('/attachments/{mailId}/{attachmentId}', [InternalMailController::class, 'downloadAttachment']);
    $router->post('/{mailId}/read', [InternalMailController::class, 'markRead']);
    $router->post('/{mailId}/archive', [InternalMailController::class, 'archive']);
    $router->post('/{mailId}/restore', [InternalMailController::class, 'restore']);
});

$router->group('/services', function ($router): void {
    $router->get('/', [InfrastructureController::class, 'index']);
    $router->get('/fileserver', [InfrastructureController::class, 'fileBrowser']);
});

$router->group('/users', function ($router): void {
    $router->get('/', [UserController::class, 'index']);
    $router->get('/audit', [UserController::class, 'audit']);
    $router->post('/{id}/reset-password', [UserController::class, 'resetPassword']);
    $router->post('/{id}/assignment', [UserController::class, 'updateAssignment']);
});

$router->group('/departments', function ($router): void {
    $router->get('/', [DepartmentController::class, 'index']);
    $router->get('/{slug}', [DepartmentController::class, 'show']);
    $router->post('/{slug}/documents', [DepartmentController::class, 'storeDocument']);
    $router->post('/{slug}/people', [DepartmentController::class, 'storeManagedPerson']);
    $router->post('/{slug}/upload', [DepartmentController::class, 'uploadFile']);
    $router->get('/{slug}/files/open', [DepartmentController::class, 'openDepartmentFile']);

    $router->group('/{slug}/employees', function ($router): void {
        $router->post('/', [DepartmentController::class, 'storeEmployee']);
        $router->post('/{employeeId}/update', [DepartmentController::class, 'updateEmployee']);
        $router->post('/{employeeId}/delete', [DepartmentController::class, 'destroyEmployee']);
        $router->post('/documents', [DepartmentController::class, 'uploadEmployeeDocument']);
        $router->post('/{employeeId}/documents/{documentId}/delete', [DepartmentController::class, 'destroyEmployeeDocument']);
        $router->get('/{employeeId}/documents/{documentId}', [DepartmentController::class, 'downloadEmployeeDocument']);
    });
});

$router->group('/', function ($router): void {
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/password/change', [AuthController::class, 'showPasswordChange']);
    $router->post('/password/change', [AuthController::class, 'changePassword']);
    $router->post('/logout', [AuthController::class, 'logout']);
});
