<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Database;

$_SERVER['REQUEST_METHOD'] ??= 'CLI';
$_SERVER['REQUEST_URI'] ??= '/cli';

$config = require __DIR__ . '/app.php';
$databaseConfig = $config['database']['connections'][$config['database']['default']] ?? null;

if ($databaseConfig === null) {
    throw new RuntimeException('Database configuration is missing.');
}

Database::connect($databaseConfig);

return new App($config);
