<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    public static function findByEmail(string $email): ?array
    {
        $statement = self::pdo()->prepare(
            'SELECT users.id, users.name, users.email, users.password_hash, users.email_verified_at, roles.name AS role_name
             FROM users
             LEFT JOIN roles ON roles.id = users.role_id
             WHERE users.email = :email
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public static function findById(int $id): ?array
    {
        $statement = self::pdo()->prepare(
            'SELECT users.id, users.name, users.email, users.email_verified_at, roles.name AS role_name
             FROM users
             LEFT JOIN roles ON roles.id = users.role_id
             WHERE users.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public static function findByEmails(array $emails): array
    {
        $emails = array_values(array_unique(array_filter(array_map('trim', $emails))));

        if ($emails === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($emails), '?'));
        $statement = self::pdo()->prepare(
            "SELECT id, name, email
             FROM users
             WHERE email IN ($placeholders)
             ORDER BY name"
        );
        $statement->execute($emails);

        return $statement->fetchAll() ?: [];
    }

    public static function setVerificationToken(int $id, string $token): void
    {
        $statement = self::pdo()->prepare(
            'UPDATE users
             SET email_verification_token = :token,
                 email_verification_sent_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'token' => hash('sha256', $token),
        ]);
    }

    public static function verifyEmail(int $id): void
    {
        $statement = self::pdo()->prepare(
            'UPDATE users
             SET email_verified_at = NOW(),
                 email_verification_token = NULL,
                 email_verification_sent_at = NULL
             WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }

    public static function findForVerification(int $id): ?array
    {
        $statement = self::pdo()->prepare(
            'SELECT id, name, email, email_verified_at, email_verification_token
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public static function internalDirectory(): array
    {
        $statement = self::pdo()->query(
            'SELECT users.id,
                    users.name,
                    users.email,
                    roles.name AS role_name,
                    departments.name AS department_name,
                    departments.slug AS department_slug,
                    department_user.membership_role
             FROM users
             LEFT JOIN roles ON roles.id = users.role_id
             LEFT JOIN department_user ON department_user.user_id = users.id
             LEFT JOIN departments ON departments.id = department_user.department_id
             ORDER BY departments.name IS NULL, departments.name, users.name'
        );

        return $statement->fetchAll() ?: [];
    }

    private static function pdo(): PDO
    {
        return Database::instance()->pdo();
    }
}
