#!/usr/bin/env php
<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/bootstrap/app.php';
$appConfig = $config['app'] ?? [];
$databaseKey = $config['database']['default'] ?? null;
$databaseConfig = is_string($databaseKey)
    ? ($config['database']['connections'][$databaseKey] ?? null)
    : null;

if (!is_array($databaseConfig)) {
    fwrite(STDERR, "Database configuration is missing.\n");
    exit(1);
}

$appEnv = (string) ($appConfig['env'] ?? 'production');
$ci = filter_var((string) env('CI', false), FILTER_VALIDATE_BOOL);

if ($appEnv !== 'testing' && $ci !== true) {
    fwrite(STDERR, "Refusing to reset the database outside APP_ENV=testing or CI.\n");
    exit(1);
}

$driver = (string) ($databaseConfig['driver'] ?? 'mysql');
$host = (string) ($databaseConfig['host'] ?? '127.0.0.1');
$port = (int) ($databaseConfig['port'] ?? 3306);
$database = (string) ($databaseConfig['database'] ?? '');
$username = (string) ($databaseConfig['username'] ?? '');
$password = (string) ($databaseConfig['password'] ?? '');
$charset = (string) ($databaseConfig['charset'] ?? 'utf8mb4');
$collation = (string) ($databaseConfig['collation'] ?? 'utf8mb4_unicode_ci');

if ($driver !== 'mysql') {
    fwrite(STDERR, sprintf("Unsupported database driver: %s\n", $driver));
    exit(1);
}

if ($database === '') {
    fwrite(STDERR, "Database name is required.\n");
    exit(1);
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $charset) || !preg_match('/^[a-zA-Z0-9_]+$/', $collation)) {
    fwrite(STDERR, "Database charset or collation contains unsupported characters.\n");
    exit(1);
}

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

if (defined('PDO::MYSQL_ATTR_MULTI_STATEMENTS')) {
    $options[PDO::MYSQL_ATTR_MULTI_STATEMENTS] = true;
}

$dsn = sprintf('%s:host=%s;port=%d;charset=%s', $driver, $host, $port, $charset);
$migrationFiles = sortedSqlFiles(dirname(__DIR__) . '/database/migrations/*.sql');
$seedFiles = sortedSqlFiles(dirname(__DIR__) . '/database/seeds/*.sql');

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    $quotedDatabase = '`' . str_replace('`', '``', $database) . '`';

    $pdo->exec('DROP DATABASE IF EXISTS ' . $quotedDatabase);
    $pdo->exec(sprintf(
        'CREATE DATABASE %s CHARACTER SET %s COLLATE %s',
        $quotedDatabase,
        $charset,
        $collation
    ));
    $pdo->exec('USE ' . $quotedDatabase);

    executeSqlFiles($pdo, $migrationFiles);
    executeSqlFiles($pdo, $seedFiles);
} catch (Throwable $throwable) {
    fwrite(STDERR, 'Test database bootstrap failed: ' . $throwable->getMessage() . "\n");
    exit(1);
}

echo sprintf(
    "Bootstrapped test database %s with %d migrations and %d seeds.\n",
    $database,
    count($migrationFiles),
    count($seedFiles)
);

function sortedSqlFiles(string $pattern): array
{
    $files = glob($pattern) ?: [];
    sort($files);

    return $files;
}

function executeSqlFiles(PDO $pdo, array $files): void
{
    foreach ($files as $path) {
        $sql = file_get_contents($path);

        if (!is_string($sql) || trim($sql) === '') {
            throw new RuntimeException('SQL file could not be loaded: ' . $path);
        }

        $pdo->exec($sql);
    }
}
