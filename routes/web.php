<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\DepartmentController;
use App\Controllers\InfrastructureController;
use App\Controllers\MailController;
use App\Controllers\InternalMailController;
use App\Controllers\PageController;

$router = $app->router();

$router->get('/', [PageController::class, 'news']);
$router->get('/news', [PageController::class, 'news']);
$router->get('/calendar', [PageController::class, 'calendar']);
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/mail', [InternalMailController::class, 'index']);
$router->post('/mail/send', [InternalMailController::class, 'send']);
$router->post('/mail/demo-send', [MailController::class, 'sendDemo']);
$router->get('/services', [InfrastructureController::class, 'index']);
$router->get('/departments', [DepartmentController::class, 'index']);
$router->get('/departments/{slug}', [DepartmentController::class, 'show']);
$router->post('/departments/{slug}/documents', [DepartmentController::class, 'storeDocument']);
$router->post('/departments/{slug}/upload', [DepartmentController::class, 'uploadFile']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);
