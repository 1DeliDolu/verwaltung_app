<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\PageController;

$router = $app->router();

$router->get('/', [PageController::class, 'news']);
$router->get('/news', [PageController::class, 'news']);
$router->get('/calendar', [PageController::class, 'calendar']);
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);
