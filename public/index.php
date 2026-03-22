<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Database;

$config = require dirname(__DIR__) . '/bootstrap/app.php';
$databaseConfig = $config['database']['connections'][$config['database']['default']] ?? null;

if ($databaseConfig === null) {
    http_response_code(500);
    echo 'Database configuration is missing.';
    exit;
}

Database::connect($databaseConfig);

$app = new App($config);
require dirname(__DIR__) . '/routes/web.php';
$app->run();
