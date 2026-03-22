<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?self $instance = null;

    private PDO $connection;

    private function __construct(array $config)
    {
        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? '127.0.0.1';
        $port = (int) ($config['port'] ?? 3306);
        $database = $config['database'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';

        if ($driver !== 'mysql') {
            throw new RuntimeException(sprintf('Unsupported database driver: %s', $driver));
        }

        $dsn = sprintf('%s:host=%s;port=%d;dbname=%s;charset=%s', $driver, $host, $port, $database, $charset);

        try {
            $this->connection = new PDO(
                $dsn,
                $config['username'] ?? '',
                $config['password'] ?? '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Database connection failed: ' . $exception->getMessage(),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public static function connect(array $config): self
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            throw new RuntimeException('Database has not been connected yet.');
        }

        return self::$instance;
    }

    public function pdo(): PDO
    {
        return $this->connection;
    }

    public function ping(): bool
    {
        $statement = $this->connection->query('SELECT 1');

        return $statement !== false;
    }
}
