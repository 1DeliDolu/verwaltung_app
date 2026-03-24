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
            'SELECT users.id,
                    users.name,
                    users.email,
                    users.password_hash,
                    users.email_verified_at,
                    users.password_change_required_at,
                    users.password_changed_at,
                    users.created_by_user_id,
                    roles.name AS role_name
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
            'SELECT users.id,
                    users.name,
                    users.email,
                    users.email_verified_at,
                    users.password_change_required_at,
                    users.password_changed_at,
                    users.created_by_user_id,
                    roles.name AS role_name
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

    public static function createProvisionedAccount(array $payload): int
    {
        $statement = self::pdo()->prepare(
            'INSERT INTO users (
                name,
                email,
                password_hash,
                role_id,
                created_by_user_id,
                password_change_required_at,
                password_changed_at
            ) VALUES (
                :name,
                :email,
                :password_hash,
                (SELECT id FROM roles WHERE name = :role_name),
                :created_by_user_id,
                NOW(),
                NULL
            )'
        );
        $statement->execute($payload);

        return (int) self::pdo()->lastInsertId();
    }

    public static function addDepartmentMembership(int $userId, int $departmentId, string $membershipRole): void
    {
        $statement = self::pdo()->prepare(
            'INSERT INTO department_user (
                department_id,
                user_id,
                membership_role
            ) VALUES (
                :department_id,
                :user_id,
                :membership_role
            )
            ON DUPLICATE KEY UPDATE
                membership_role = VALUES(membership_role)'
        );
        $statement->execute([
            'department_id' => $departmentId,
            'user_id' => $userId,
            'membership_role' => $membershipRole,
        ]);
    }

    public static function eligibleForPersonnelProfiles(): array
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
             INNER JOIN roles ON roles.id = users.role_id
             LEFT JOIN department_user ON department_user.user_id = users.id
             LEFT JOIN departments ON departments.id = department_user.department_id
             LEFT JOIN employees ON employees.user_id = users.id
             WHERE roles.name <> \'admin\'
               AND users.created_by_user_id IS NOT NULL
               AND employees.id IS NULL
             ORDER BY departments.name IS NULL, departments.name, users.name'
        );

        return $statement->fetchAll() ?: [];
    }

    public static function updatePassword(int $userId, string $passwordHash): void
    {
        $statement = self::pdo()->prepare(
            'UPDATE users
             SET password_hash = :password_hash,
                 password_change_required_at = NULL,
                 password_changed_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $userId,
            'password_hash' => $passwordHash,
        ]);
    }

    public static function countProvisionedForDepartment(int $departmentId): int
    {
        $statement = self::pdo()->prepare(
            'SELECT COUNT(*) AS aggregate_count
             FROM users
             INNER JOIN department_user ON department_user.user_id = users.id
             WHERE department_user.department_id = :department_id
               AND users.created_by_user_id IS NOT NULL'
        );
        $statement->execute(['department_id' => $departmentId]);
        $row = $statement->fetch() ?: [];

        return (int) ($row['aggregate_count'] ?? 0);
    }

    private static function pdo(): PDO
    {
        return Database::instance()->pdo();
    }
}
