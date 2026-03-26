<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class LoginEmailChallenge
{
    public static function create(array $payload): int
    {
        $statement = self::pdo()->prepare(
            'INSERT INTO login_email_challenges (
                user_id,
                code_hash,
                requested_ip,
                requested_user_agent,
                expires_at
            ) VALUES (
                :user_id,
                :code_hash,
                :requested_ip,
                :requested_user_agent,
                :expires_at
            )'
        );
        $statement->execute([
            'user_id' => $payload['user_id'],
            'code_hash' => $payload['code_hash'],
            'requested_ip' => $payload['requested_ip'],
            'requested_user_agent' => $payload['requested_user_agent'],
            'expires_at' => $payload['expires_at'],
        ]);

        return (int) self::pdo()->lastInsertId();
    }

    public static function invalidateUnusedForUser(int $userId, string $consumedAt): void
    {
        $statement = self::pdo()->prepare(
            'UPDATE login_email_challenges
             SET consumed_at = :consumed_at
             WHERE user_id = :user_id
               AND consumed_at IS NULL'
        );
        $statement->execute([
            'user_id' => $userId,
            'consumed_at' => $consumedAt,
        ]);
    }

    public static function findActiveById(int $challengeId): ?array
    {
        $statement = self::pdo()->prepare(
            'SELECT id, user_id, code_hash, expires_at, consumed_at
             FROM login_email_challenges
             WHERE id = :id
               AND consumed_at IS NULL
             LIMIT 1'
        );
        $statement->execute(['id' => $challengeId]);
        $challenge = $statement->fetch();

        return $challenge === false ? null : $challenge;
    }

    public static function markConsumed(int $challengeId, string $consumedAt): void
    {
        $statement = self::pdo()->prepare(
            'UPDATE login_email_challenges
             SET consumed_at = :consumed_at
             WHERE id = :id
               AND consumed_at IS NULL'
        );
        $statement->execute([
            'id' => $challengeId,
            'consumed_at' => $consumedAt,
        ]);
    }

    private static function pdo(): PDO
    {
        return Database::instance()->pdo();
    }
}
