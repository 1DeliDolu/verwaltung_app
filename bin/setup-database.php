#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Services\DatabaseSetupService;

$config = require dirname(__DIR__) . '/bootstrap/app.php';
$options = getopt('', [
    'fresh',
    'dry-run',
    'migrate-only',
    'seed-only',
    'help',
]);

if ($options === false) {
    fwrite(STDERR, "Options could not be read.\n");
    exit(1);
}

if (isset($options['help'])) {
    echo <<<TEXT
Usage:
  php bin/setup-database.php [options]

Options:
  --fresh         Drop and recreate the configured database before applying files.
  --dry-run       Show the planned database actions without applying them.
  --migrate-only  Apply only pending migrations.
  --seed-only     Apply only pending seeds.
  --help          Show this help.

TEXT;
    exit(0);
}

$databaseKey = $config['database']['default'] ?? null;
$databaseConfig = is_string($databaseKey)
    ? ($config['database']['connections'][$databaseKey] ?? null)
    : null;

if (!is_array($databaseConfig)) {
    fwrite(STDERR, "Database configuration is missing.\n");
    exit(1);
}

$appEnv = (string) ($config['app']['env'] ?? 'production');
$ci = filter_var((string) env('CI', false), FILTER_VALIDATE_BOOL);
$fresh = isset($options['fresh']);
$dryRun = isset($options['dry-run']);

try {
    if ($fresh) {
        DatabaseSetupService::assertFreshAllowed($appEnv, $ci === true);
    }

    $mode = DatabaseSetupService::resolveExecutionMode(
        isset($options['migrate-only']),
        isset($options['seed-only'])
    );

    $service = new DatabaseSetupService($databaseConfig);
    $result = $service->setup(
        $mode['migrations'],
        $mode['seeds'],
        $fresh,
        $dryRun
    );
} catch (Throwable $throwable) {
    fwrite(STDERR, 'Database setup failed: ' . $throwable->getMessage() . "\n");
    exit(1);
}

if ($dryRun) {
    echo "Database setup dry run completed.\n";
    echo 'Database: ' . (string) ($result['database'] ?? '') . "\n";
    echo 'Database exists: ' . ((bool) ($result['database_exists'] ?? false) ? 'yes' : 'no') . "\n";
    echo 'Legacy state adoption: ' . ((bool) ($result['legacy_adopted'] ?? false) ? 'yes' : 'no') . "\n";
    echo 'Pending migrations: ' . (string) ($result['pending_migrations'] ?? 0) . "\n";
    echo 'Pending seeds: ' . (string) ($result['pending_seeds'] ?? 0) . "\n";

    if (($result['migration_files'] ?? []) !== []) {
        echo 'Migration files: ' . implode(', ', (array) $result['migration_files']) . "\n";
    }

    if (($result['seed_files'] ?? []) !== []) {
        echo 'Seed files: ' . implode(', ', (array) $result['seed_files']) . "\n";
    }

    exit(0);
}

echo "Database setup completed.\n";
echo 'Database: ' . (string) ($result['database'] ?? '') . "\n";
echo 'Database created: ' . ((bool) ($result['database_created'] ?? false) ? 'yes' : 'no') . "\n";
echo 'Legacy state adoption: ' . ((bool) ($result['legacy_adopted'] ?? false) ? 'yes' : 'no') . "\n";
echo 'Applied migrations: ' . (string) ($result['applied_migrations'] ?? 0) . "\n";
echo 'Applied seeds: ' . (string) ($result['applied_seeds'] ?? 0) . "\n";
