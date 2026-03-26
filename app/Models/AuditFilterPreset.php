<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class AuditFilterPreset
{
    public static function forUser(int $userId): array
    {
        $statement = self::pdo()->prepare(
            'SELECT id,
                    user_id,
                    name,
                    source,
                    search,
                    outcome,
                    date_from,
                    date_to,
                    created_at,
                    updated_at
             FROM audit_filter_presets
             WHERE user_id = :user_id
             ORDER BY updated_at DESC, name ASC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll() ?: [];
    }

    public static function upsert(array $payload): int
    {
        $statement = self::pdo()->prepare(
            'INSERT INTO audit_filter_presets (
                user_id,
                name,
                source,
                search,
                outcome,
                date_from,
                date_to
            ) VALUES (
                :user_id,
                :name,
                :source,
                :search,
                :outcome,
                :date_from,
                :date_to
            )
            ON DUPLICATE KEY UPDATE
                source = VALUES(source),
                search = VALUES(search),
                outcome = VALUES(outcome),
                date_from = VALUES(date_from),
                date_to = VALUES(date_to),
                updated_at = NOW(),
                id = LAST_INSERT_ID(id)'
        );
        $statement->execute([
            'user_id' => $payload['user_id'],
            'name' => $payload['name'],
            'source' => $payload['source'],
            'search' => $payload['search'],
            'outcome' => $payload['outcome'],
            'date_from' => $payload['date_from'],
            'date_to' => $payload['date_to'],
        ]);

        return (int) self::pdo()->lastInsertId();
    }

    public static function deleteForUser(int $userId, int $presetId): bool
    {
        $statement = self::pdo()->prepare(
            'DELETE FROM audit_filter_presets
             WHERE id = :id
               AND user_id = :user_id'
        );
        $statement->execute([
            'id' => $presetId,
            'user_id' => $userId,
        ]);

        return $statement->rowCount() > 0;
    }

    private static function pdo(): PDO
    {
        return Database::instance()->pdo();
    }
}
