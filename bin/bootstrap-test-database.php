#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Services\DatabaseSetupService;

$config = require dirname(__DIR__) . '/bootstrap/app.php';
$databaseKey = $config['database']['default'] ?? null;
$databaseConfig = is_string($databaseKey)
    ? ($config['database']['connections'][$databaseKey] ?? null)
    : null;

try {
    if (!is_array($databaseConfig)) {
        throw new RuntimeException('Database configuration is missing.');
    }

    $appEnv = (string) ($config['app']['env'] ?? 'production');
    $ci = filter_var((string) env('CI', false), FILTER_VALIDATE_BOOL);

    DatabaseSetupService::assertFreshAllowed($appEnv, $ci === true);

    $service = new DatabaseSetupService($databaseConfig);
    $result = $service->freshBootstrap();
} catch (Throwable $throwable) {
    fwrite(STDERR, 'Test database bootstrap failed: ' . $throwable->getMessage() . "\n");
    exit(1);
}

echo sprintf(
    "Bootstrapped test database %s with %d migrations and %d seeds.\n",
    (string) ($result['database'] ?? ''),
    (int) ($result['applied_migrations'] ?? 0),
    (int) ($result['applied_seeds'] ?? 0)
);
