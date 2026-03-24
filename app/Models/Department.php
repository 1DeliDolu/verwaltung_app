<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Department
{
    public static function all(): array
    {
        $statement = self::pdo()->query(
            'SELECT id, name, slug, description
             FROM departments
             ORDER BY name'
        );

        return $statement->fetchAll() ?: [];
    }

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

    public static function findById(int $id): ?array
    {
        $statement = self::pdo()->prepare(
            'SELECT id, name, slug, description
             FROM departments
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $department = $statement->fetch();

        return $department === false ? null : $department;
    }

    public static function membersForIds(array $departmentIds, int $excludeUserId = 0): array
    {
        if ($departmentIds === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($departmentIds), '?'));
        $query = "SELECT DISTINCT users.id, users.name, users.email
                  FROM users
                  INNER JOIN department_user ON department_user.user_id = users.id
                  WHERE department_user.department_id IN ($placeholders)";

        $params = $departmentIds;

        if ($excludeUserId > 0) {
            $query .= ' AND users.id <> ?';
            $params[] = $excludeUserId;
        }

        $query .= ' ORDER BY users.name';

        $statement = self::pdo()->prepare($query);
        $statement->execute($params);

        return $statement->fetchAll() ?: [];
    }

    private static function pdo(): PDO
    {
        return Database::instance()->pdo();
    }
}
