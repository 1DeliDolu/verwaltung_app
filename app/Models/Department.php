<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Department
{
    public static function allVisibleForUser(int $userId, bool $isAdmin): array
    {
        if ($isAdmin) {
            $statement = self::pdo()->query(
                'SELECT id, name, slug, description
                 FROM departments
                 ORDER BY name'
            );

            return $statement->fetchAll() ?: [];
        }

        $statement = self::pdo()->prepare(
            'SELECT departments.id, departments.name, departments.slug, departments.description, department_user.membership_role
             FROM departments
             INNER JOIN department_user ON department_user.department_id = departments.id
             WHERE department_user.user_id = :user_id
             ORDER BY departments.name'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll() ?: [];
    }

    public static function findVisibleForUser(string $slug, int $userId, bool $isAdmin): ?array
    {
        if ($isAdmin) {
            $statement = self::pdo()->prepare(
                'SELECT id, name, slug, description
                 FROM departments
                 WHERE slug = :slug
                 LIMIT 1'
            );
            $statement->execute(['slug' => $slug]);
            $department = $statement->fetch();

            return $department === false ? null : $department;
        }

        $statement = self::pdo()->prepare(
            'SELECT departments.id, departments.name, departments.slug, departments.description, department_user.membership_role
             FROM departments
             INNER JOIN department_user ON department_user.department_id = departments.id
             WHERE departments.slug = :slug AND department_user.user_id = :user_id
             LIMIT 1'
        );
        $statement->execute([
            'slug' => $slug,
            'user_id' => $userId,
        ]);
        $department = $statement->fetch();

        return $department === false ? null : $department;
    }

    private static function pdo(): PDO
    {
        return Database::instance()->pdo();
    }
}
