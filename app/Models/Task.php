<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Task
{
    public static function visibleForUser(int $userId, bool $isAdmin, array $filters = []): array
    {
        $params = [];
        $query = 'SELECT tasks.id,
                         tasks.department_id,
                         tasks.title,
                         tasks.description,
                         tasks.status,
                         tasks.priority,
                         tasks.due_date,
                         tasks.created_at,
                         tasks.updated_at,
                         departments.name AS department_name,
                         departments.slug AS department_slug,
                         creators.name AS creator_name,
                         creators.email AS creator_email,
                         assignees.id AS assignee_id,
                         assignees.name AS assignee_name,
                         assignees.email AS assignee_email
                  FROM tasks
                  INNER JOIN departments ON departments.id = tasks.department_id
                  INNER JOIN users AS creators ON creators.id = tasks.created_by_user_id
                  LEFT JOIN users AS assignees ON assignees.id = tasks.assigned_to_user_id ';

        if ($isAdmin) {
            $query .= 'WHERE 1 = 1 ';
        } else {
            $query .= 'WHERE EXISTS (
                            SELECT 1
                            FROM department_user
                            WHERE department_user.department_id = tasks.department_id
                              AND department_user.user_id = :viewer_user_id
                        ) ';
            $params['viewer_user_id'] = $userId;
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            $query .= 'AND tasks.status = :status ';
            $params['status'] = $status;
        }

        $departmentId = (int) ($filters['department_id'] ?? 0);
        if ($departmentId > 0) {
            $query .= 'AND tasks.department_id = :department_id ';
            $params['department_id'] = $departmentId;
        }

        $query .= 'ORDER BY
                     CASE tasks.priority
                         WHEN \'urgent\' THEN 1
                         WHEN \'high\' THEN 2
                         WHEN \'normal\' THEN 3
                         ELSE 4
                     END,
                     tasks.due_date IS NULL,
                     tasks.due_date,
                     tasks.created_at DESC';

        $limit = (int) ($filters['limit'] ?? 0);
        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit;
        }

        $statement = self::pdo()->prepare($query);
        $statement->execute($params);

        return $statement->fetchAll() ?: [];
    }

    public static function findVisibleForUser(int $taskId, int $userId, bool $isAdmin): ?array
    {
        $params = ['task_id' => $taskId];
        $query = 'SELECT tasks.id,
                         tasks.department_id,
                         tasks.title,
                         tasks.description,
                         tasks.status,
                         tasks.priority,
                         tasks.due_date,
                         tasks.created_by_user_id,
                         tasks.assigned_to_user_id,
                         tasks.created_at,
                         tasks.updated_at,
                         departments.name AS department_name,
                         departments.slug AS department_slug,
                         creators.name AS creator_name,
                         creators.email AS creator_email,
                         assignees.name AS assignee_name,
                         assignees.email AS assignee_email
                  FROM tasks
                  INNER JOIN departments ON departments.id = tasks.department_id
                  INNER JOIN users AS creators ON creators.id = tasks.created_by_user_id
                  LEFT JOIN users AS assignees ON assignees.id = tasks.assigned_to_user_id ';

        if ($isAdmin) {
            $query .= 'WHERE tasks.id = :task_id ';
        } else {
            $query .= 'INNER JOIN department_user
                           ON department_user.department_id = tasks.department_id
                          AND department_user.user_id = :viewer_user_id
                      WHERE tasks.id = :task_id ';
            $params['viewer_user_id'] = $userId;
        }

        $query .= 'LIMIT 1';

        $statement = self::pdo()->prepare($query);
        $statement->execute($params);
        $task = $statement->fetch();

        return $task === false ? null : $task;
    }

    public static function create(array $payload): int
    {
        $statement = self::pdo()->prepare(
            'INSERT INTO tasks (
                department_id,
                title,
                description,
                status,
                priority,
                due_date,
                created_by_user_id,
                assigned_to_user_id
            ) VALUES (
                :department_id,
                :title,
                :description,
                :status,
                :priority,
                :due_date,
                :created_by_user_id,
                :assigned_to_user_id
            )'
        );
        $statement->execute($payload);

        return (int) self::pdo()->lastInsertId();
    }

    public static function updateTask(int $taskId, array $payload): void
    {
        $payload['id'] = $taskId;

        $statement = self::pdo()->prepare(
            'UPDATE tasks
             SET department_id = :department_id,
                 title = :title,
                 description = :description,
                 priority = :priority,
                 due_date = :due_date,
                 assigned_to_user_id = :assigned_to_user_id
             WHERE id = :id'
        );
        $statement->execute($payload);
    }

    public static function updateStatus(int $taskId, string $status): void
    {
        $statement = self::pdo()->prepare(
            'UPDATE tasks
             SET status = :status
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $taskId,
            'status' => $status,
        ]);
    }

    public static function countByStatusForUser(int $userId, bool $isAdmin, array $filters = []): array
    {
        $params = [];
        $query = 'SELECT tasks.status, COUNT(*) AS aggregate_count
                  FROM tasks ';

        if ($isAdmin) {
            $query .= 'WHERE 1 = 1 ';
        } else {
            $query .= 'WHERE EXISTS (
                            SELECT 1
                            FROM department_user
                            WHERE department_user.department_id = tasks.department_id
                              AND department_user.user_id = :viewer_user_id
                        ) ';
            $params['viewer_user_id'] = $userId;
        }

        $departmentId = (int) ($filters['department_id'] ?? 0);
        if ($departmentId > 0) {
            $query .= 'AND tasks.department_id = :department_id ';
            $params['department_id'] = $departmentId;
        }

        $query .= 'GROUP BY tasks.status';

        $statement = self::pdo()->prepare($query);
        $statement->execute($params);
        $rows = $statement->fetchAll() ?: [];
        $counts = [];

        foreach ($rows as $row) {
            $counts[(string) $row['status']] = (int) $row['aggregate_count'];
        }

        return $counts;
    }

    private static function pdo(): PDO
    {
        return Database::instance()->pdo();
    }
}
