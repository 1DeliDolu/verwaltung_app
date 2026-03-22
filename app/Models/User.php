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
            'SELECT users.id, users.name, users.email, users.password_hash, roles.name AS role_name
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
            'SELECT users.id, users.name, users.email, roles.name AS role_name
             FROM users
             LEFT JOIN roles ON roles.id = users.role_id
             WHERE users.id = :id
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
