<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use RuntimeException;

final class DatabaseSetupService
{
    public const MIGRATION_TRACKING_TABLE = 'schema_migrations';
    public const SEED_TRACKING_TABLE = 'database_seed_runs';

    public function __construct(private readonly array $databaseConfig)
    {
    }

    public static function assertFreshAllowed(string $appEnv, bool $ci): void
    {
        if ($appEnv !== 'testing' && $ci !== true) {
            throw new RuntimeException('Refusing fresh database setup outside APP_ENV=testing or CI.');
        }
    }

    public static function resolveExecutionMode(bool $migrateOnly, bool $seedOnly): array
    {
        if ($migrateOnly && $seedOnly) {
            throw new RuntimeException('Cannot combine --migrate-only and --seed-only.');
        }

        if ($migrateOnly) {
            return ['migrations' => true, 'seeds' => false];
        }

        if ($seedOnly) {
            return ['migrations' => false, 'seeds' => true];
        }

        return ['migrations' => true, 'seeds' => true];
    }

    public function setup(bool $includeMigrations = true, bool $includeSeeds = true, bool $fresh = false, bool $dryRun = false): array
    {
        if (!$includeMigrations && !$includeSeeds) {
            throw new RuntimeException('At least one of migrations or seeds must be selected.');
        }

        if ($fresh && !$includeMigrations) {
            throw new RuntimeException('Fresh setup requires migrations.');
        }

        if ($dryRun) {
            return $this->dryRun($includeMigrations, $includeSeeds, $fresh);
        }

        $databaseCreated = false;

        if ($fresh) {
            $this->recreateDatabase();
            $databaseCreated = true;
        } else {
            $databaseCreated = $this->ensureDatabaseExists();
        }

        $pdo = $this->connectDatabase();
        $legacyAdopted = $fresh ? false : $this->adoptLegacyState($pdo, false);
        $this->ensureTrackingTables($pdo);

        $pendingMigrations = $this->pendingFiles($pdo, self::MIGRATION_TRACKING_TABLE, $this->migrationFiles());

        if (!$includeMigrations && $includeSeeds && $pendingMigrations !== []) {
            throw new RuntimeException('Pending migrations exist. Run database setup without --seed-only first.');
        }

        $pendingSeeds = $includeSeeds
            ? $this->pendingFiles($pdo, self::SEED_TRACKING_TABLE, $this->seedFiles())
            : [];

        $appliedMigrations = $includeMigrations
            ? $this->applyFiles($pdo, self::MIGRATION_TRACKING_TABLE, $pendingMigrations)
            : 0;
        $appliedSeeds = $includeSeeds
            ? $this->applyFiles($pdo, self::SEED_TRACKING_TABLE, $pendingSeeds)
            : 0;

        return [
            'database' => $this->databaseName(),
            'fresh' => $fresh,
            'dry_run' => false,
            'database_created' => $databaseCreated,
            'legacy_adopted' => $legacyAdopted,
            'pending_migrations' => count($pendingMigrations),
            'pending_seeds' => count($pendingSeeds),
            'applied_migrations' => $appliedMigrations,
            'applied_seeds' => $appliedSeeds,
        ];
    }

    public function freshBootstrap(): array
    {
        return $this->setup(true, true, true, false);
    }

    private function dryRun(bool $includeMigrations, bool $includeSeeds, bool $fresh): array
    {
        $server = $this->connectServer();
        $databaseExists = $fresh ? false : $this->databaseExists($server);
        $pendingMigrations = $includeMigrations ? $this->migrationFiles() : [];
        $pendingSeeds = $includeSeeds ? $this->seedFiles() : [];

        if ($databaseExists) {
            $pdo = $this->connectDatabase();
            $legacyAdopted = $this->adoptLegacyState($pdo, true);
            $pendingMigrations = $legacyAdopted
                ? []
                : ($includeMigrations
                    ? $this->pendingFiles($pdo, self::MIGRATION_TRACKING_TABLE, $this->migrationFiles(), false)
                    : []);

            if (!$includeMigrations && $includeSeeds && $pendingMigrations !== []) {
                throw new RuntimeException('Pending migrations exist. Run database setup without --seed-only first.');
            }

            $pendingSeeds = $legacyAdopted
                ? []
                : ($includeSeeds
                    ? $this->pendingFiles($pdo, self::SEED_TRACKING_TABLE, $this->seedFiles(), false)
                    : []);
        } elseif (!$includeMigrations && $includeSeeds) {
            throw new RuntimeException('Database does not exist. Run database setup without --seed-only first.');
        }

        return [
            'database' => $this->databaseName(),
            'fresh' => $fresh,
            'dry_run' => true,
            'database_exists' => $databaseExists,
            'legacy_adopted' => $databaseExists ? ($legacyAdopted ?? false) : false,
            'pending_migrations' => count($pendingMigrations),
            'pending_seeds' => count($pendingSeeds),
            'migration_files' => array_map('basename', $pendingMigrations),
            'seed_files' => array_map('basename', $pendingSeeds),
        ];
    }

    private function migrationFiles(): array
    {
        return $this->sortedSqlFiles(BASE_PATH . '/database/migrations/*.sql');
    }

    private function seedFiles(): array
    {
        return $this->sortedSqlFiles(BASE_PATH . '/database/seeds/*.sql');
    }

    private function sortedSqlFiles(string $pattern): array
    {
        $files = glob($pattern) ?: [];
        sort($files);

        return $files;
    }

    private function ensureDatabaseExists(): bool
    {
        $server = $this->connectServer();

        if ($this->databaseExists($server)) {
            return false;
        }

        $server->exec(sprintf(
            'CREATE DATABASE %s CHARACTER SET %s COLLATE %s',
            $this->quotedDatabaseName(),
            $this->charset(),
            $this->collation()
        ));

        return true;
    }

    private function recreateDatabase(): void
    {
        $server = $this->connectServer();

        $server->exec('DROP DATABASE IF EXISTS ' . $this->quotedDatabaseName());
        $server->exec(sprintf(
            'CREATE DATABASE %s CHARACTER SET %s COLLATE %s',
            $this->quotedDatabaseName(),
            $this->charset(),
            $this->collation()
        ));
    }

    private function databaseExists(PDO $server): bool
    {
        $statement = $server->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :database');
        $statement->execute(['database' => $this->databaseName()]);

        return $statement->fetchColumn() !== false;
    }

    private function connectServer(): PDO
    {
        return $this->createPdo(false);
    }

    private function connectDatabase(): PDO
    {
        return $this->createPdo(true);
    }

    private function createPdo(bool $withDatabase): PDO
    {
        $driver = (string) ($this->databaseConfig['driver'] ?? 'mysql');
        $host = (string) ($this->databaseConfig['host'] ?? '127.0.0.1');
        $port = (int) ($this->databaseConfig['port'] ?? 3306);
        $charset = $this->charset();

        if ($driver !== 'mysql') {
            throw new RuntimeException(sprintf('Unsupported database driver: %s', $driver));
        }

        $dsn = sprintf('%s:host=%s;port=%d;charset=%s', $driver, $host, $port, $charset);

        if ($withDatabase) {
            $dsn = sprintf('%s;dbname=%s', $dsn, $this->databaseName());
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        if (defined('PDO::MYSQL_ATTR_MULTI_STATEMENTS')) {
            $options[PDO::MYSQL_ATTR_MULTI_STATEMENTS] = true;
        }

        return new PDO(
            $dsn,
            (string) ($this->databaseConfig['username'] ?? ''),
            (string) ($this->databaseConfig['password'] ?? ''),
            $options
        );
    }

    private function ensureTrackingTables(PDO $pdo): void
    {
        $pdo->exec(sprintf(
            'CREATE TABLE IF NOT EXISTS %s (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL UNIQUE,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=%s COLLATE=%s',
            self::MIGRATION_TRACKING_TABLE,
            $this->charset(),
            $this->collation()
        ));

        $pdo->exec(sprintf(
            'CREATE TABLE IF NOT EXISTS %s (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL UNIQUE,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=%s COLLATE=%s',
            self::SEED_TRACKING_TABLE,
            $this->charset(),
            $this->collation()
        ));
    }

    private function adoptLegacyState(PDO $pdo, bool $dryRun): bool
    {
        $hasMigrationTracking = $this->tableExists($pdo, self::MIGRATION_TRACKING_TABLE);
        $hasSeedTracking = $this->tableExists($pdo, self::SEED_TRACKING_TABLE);

        if ($hasMigrationTracking || $hasSeedTracking) {
            return false;
        }

        if (!$this->hasApplicationTables($pdo)) {
            return false;
        }

        if ($dryRun) {
            return true;
        }

        $this->ensureTrackingTables($pdo);
        $this->recordFiles($pdo, self::MIGRATION_TRACKING_TABLE, $this->migrationFiles());
        $this->recordFiles($pdo, self::SEED_TRACKING_TABLE, $this->seedFiles());

        return true;
    }

    private function pendingFiles(PDO $pdo, string $table, array $files, bool $createTrackingTables = true): array
    {
        if ($createTrackingTables) {
            $this->ensureTrackingTables($pdo);
        } elseif (!$this->tableExists($pdo, $table)) {
            return $files;
        }

        $statement = $pdo->query(sprintf('SELECT filename FROM %s', $table));
        $applied = $statement === false ? [] : array_map('strval', $statement->fetchAll(PDO::FETCH_COLUMN) ?: []);

        return array_values(array_filter($files, static function (string $path) use ($applied): bool {
            return !in_array(basename($path), $applied, true);
        }));
    }

    private function tableExists(PDO $pdo, string $table): bool
    {
        $statement = $pdo->prepare(
            'SELECT TABLE_NAME
             FROM INFORMATION_SCHEMA.TABLES
             WHERE TABLE_SCHEMA = :schema
               AND TABLE_NAME = :table
             LIMIT 1'
        );
        $statement->execute([
            'schema' => $this->databaseName(),
            'table' => $table,
        ]);

        return $statement->fetchColumn() !== false;
    }

    private function hasApplicationTables(PDO $pdo): bool
    {
        $statement = $pdo->query('SHOW TABLES');
        $tables = $statement === false ? [] : array_map('strval', $statement->fetchAll(PDO::FETCH_COLUMN) ?: []);
        $tables = array_values(array_diff($tables, [self::MIGRATION_TRACKING_TABLE, self::SEED_TRACKING_TABLE]));

        return $tables !== [];
    }

    private function applyFiles(PDO $pdo, string $table, array $files): int
    {
        foreach ($files as $path) {
            $sql = file_get_contents($path);

            if (!is_string($sql) || trim($sql) === '') {
                throw new RuntimeException('SQL file could not be loaded: ' . $path);
            }

            $pdo->exec($sql);
            $this->recordFiles($pdo, $table, [$path]);
        }

        return count($files);
    }

    private function recordFiles(PDO $pdo, string $table, array $files): void
    {
        $insert = $pdo->prepare(sprintf('INSERT IGNORE INTO %s (filename) VALUES (:filename)', $table));

        foreach ($files as $path) {
            $insert->execute(['filename' => basename($path)]);
        }
    }

    private function databaseName(): string
    {
        $database = (string) ($this->databaseConfig['database'] ?? '');

        if ($database === '') {
            throw new RuntimeException('Database name is required.');
        }

        return $database;
    }

    private function quotedDatabaseName(): string
    {
        return '`' . str_replace('`', '``', $this->databaseName()) . '`';
    }

    private function charset(): string
    {
        $charset = (string) ($this->databaseConfig['charset'] ?? 'utf8mb4');

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $charset)) {
            throw new RuntimeException('Database charset contains unsupported characters.');
        }

        return $charset;
    }

    private function collation(): string
    {
        $collation = (string) ($this->databaseConfig['collation'] ?? 'utf8mb4_unicode_ci');

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $collation)) {
            throw new RuntimeException('Database collation contains unsupported characters.');
        }

        return $collation;
    }
}
