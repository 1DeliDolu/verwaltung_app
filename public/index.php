<?php

declare(strict_types=1);

use App\Core\Database;

$config = require dirname(__DIR__) . '/bootstrap/app.php';
$databaseConfig = $config['database']['connections'][$config['database']['default']] ?? null;

if ($databaseConfig === null) {
    http_response_code(500);
    echo 'Database configuration is missing.';
    exit;
}

try {
    $database = Database::connect($databaseConfig);
    $database->ping();

    echo 'Verwaltung App database connection is ready.';
} catch (Throwable $throwable) {
    http_response_code(500);
    echo 'Database connection error: ' . $throwable->getMessage();
}
