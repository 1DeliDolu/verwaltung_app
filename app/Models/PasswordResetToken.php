<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class PasswordResetToken
{
    public static function create(array $payload): int
    {
        $statement = self::pdo()->prepare(
            'INSERT INTO password_reset_tokens (
                user_id,
                token_hash,
                requested_ip,
                requested_user_agent,
                expires_at
            ) VALUES (
                :user_id,
                :token_hash,
                :requested_ip,
                :requested_user_agent,
                :expires_at
            )'
        );
        $statement->execute([
            'user_id' => $payload['user_id'],
            'token_hash' => $payload['token_hash'],
            'requested_ip' => $payload['requested_ip'],
            'requested_user_agent' => $payload['requested_user_agent'],
            'expires_at' => $payload['expires_at'],
        ]);

        return (int) self::pdo()->lastInsertId();
    }

    public static function invalidateUnusedForUser(int $userId, string $usedAt): void
    {
        $statement = self::pdo()->prepare(
            'UPDATE password_reset_tokens
             SET used_at = :used_at
             WHERE user_id = :user_id
               AND used_at IS NULL'
        );
        $statement->execute([
            'user_id' => $userId,
            'used_at' => $usedAt,
        ]);
    }

    public static function markUsed(int $tokenId, string $usedAt): void
    {
        $statement = self::pdo()->prepare(
            'UPDATE password_reset_tokens
             SET used_at = :used_at
             WHERE id = :id
               AND used_at IS NULL'
        );
        $statement->execute([
            'id' => $tokenId,
            'used_at' => $usedAt,
        ]);
    }

    public static function findActiveByTokenHash(string $tokenHash): ?array
    {
        $statement = self::pdo()->prepare(
            'SELECT password_reset_tokens.id,
                    password_reset_tokens.user_id,
                    password_reset_tokens.expires_at,
                    users.name,
                    users.email,
                    users.password_hash
             FROM password_reset_tokens
             INNER JOIN users ON users.id = password_reset_tokens.user_id
             WHERE password_reset_tokens.token_hash = :token_hash
               AND password_reset_tokens.used_at IS NULL
             LIMIT 1'
        );
        $statement->execute(['token_hash' => $tokenHash]);
        $record = $statement->fetch();

        return $record === false ? null : $record;
    }

    private static function pdo(): PDO
    {
        return Database::instance()->pdo();
    }
}
