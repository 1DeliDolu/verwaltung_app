<?php

use App\Controllers\AuthController;
use App\Controllers\CalendarController;
use App\Controllers\DashboardController;
use App\Controllers\DepartmentController;
use App\Controllers\InfrastructureController;
use App\Controllers\MailController;
use App\Controllers\InternalMailController;
use App\Controllers\PageController;
use App\Controllers\VerificationController;

$router = $app->router();

$router->get('/', [PageController::class, 'news']);
$router->get('/news', [PageController::class, 'news']);
$router->get('/calendar', [CalendarController::class, 'index']);
$router->post('/calendar/events', [CalendarController::class, 'store']);
$router->post('/calendar/events/{id}/update', [CalendarController::class, 'update']);
$router->post('/calendar/events/{id}/delete', [CalendarController::class, 'destroy']);
$router->post('/calendar/events/{id}/complete', [CalendarController::class, 'complete']);
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/email/verify', [VerificationController::class, 'notice']);
$router->post('/email/verification-notification', [VerificationController::class, 'resend']);
$router->get('/email/verify/{id}/{token}', [VerificationController::class, 'verify']);
$router->get('/mail', [InternalMailController::class, 'index']);
$router->get('/mail/attachments/{mailId}/{attachmentId}', [InternalMailController::class, 'downloadAttachment']);
$router->post('/mail/send', [InternalMailController::class, 'send']);
$router->post('/mail/demo-send', [MailController::class, 'sendDemo']);
$router->get('/services', [InfrastructureController::class, 'index']);
$router->get('/services/fileserver', [InfrastructureController::class, 'fileBrowser']);
$router->get('/departments', [DepartmentController::class, 'index']);
$router->get('/departments/{slug}', [DepartmentController::class, 'show']);
$router->post('/departments/{slug}/documents', [DepartmentController::class, 'storeDocument']);
$router->post('/departments/{slug}/people', [DepartmentController::class, 'storeManagedPerson']);
$router->post('/departments/{slug}/employees', [DepartmentController::class, 'storeEmployee']);
$router->post('/departments/{slug}/employees/documents', [DepartmentController::class, 'uploadEmployeeDocument']);
$router->get('/departments/{slug}/employees/{employeeId}/documents/{documentId}', [DepartmentController::class, 'downloadEmployeeDocument']);
$router->get('/departments/{slug}/files/open', [DepartmentController::class, 'openDepartmentFile']);
$router->post('/departments/{slug}/upload', [DepartmentController::class, 'uploadFile']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/password/change', [AuthController::class, 'showPasswordChange']);
$router->post('/password/change', [AuthController::class, 'changePassword']);
$router->post('/logout', [AuthController::class, 'logout']);
