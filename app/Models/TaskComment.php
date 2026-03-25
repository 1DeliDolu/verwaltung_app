<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class TaskComment
{
    public static function forTask(int $taskId): array
    {
        $statement = self::pdo()->prepare(
            'SELECT task_comments.id,
                    task_comments.task_id,
                    task_comments.body,
                    task_comments.created_at,
                    users.id AS author_user_id,
                    users.name AS author_name,
                    users.email AS author_email
             FROM task_comments
             INNER JOIN users ON users.id = task_comments.author_user_id
             WHERE task_comments.task_id = :task_id
             ORDER BY task_comments.created_at ASC, task_comments.id ASC'
        );
        $statement->execute(['task_id' => $taskId]);

        return $statement->fetchAll() ?: [];
    }

    public static function create(int $taskId, int $authorUserId, string $body): void
    {
        $statement = self::pdo()->prepare(
            'INSERT INTO task_comments (
                task_id,
                author_user_id,
                body
            ) VALUES (
                :task_id,
                :author_user_id,
                :body
            )'
        );
        $statement->execute([
            'task_id' => $taskId,
            'author_user_id' => $authorUserId,
            'body' => $body,
        ]);
    }

    private static function pdo(): PDO
    {
        return Database::instance()->pdo();
    }
}
