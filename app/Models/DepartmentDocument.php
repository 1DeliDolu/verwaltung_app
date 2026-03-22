<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class DepartmentDocument
{
    public static function forDepartment(int $departmentId): array
    {
        $statement = self::pdo()->prepare(
            'SELECT department_documents.id,
                    department_documents.folder_name,
                    department_documents.title,
                    department_documents.body,
                    department_documents.created_at,
                    department_documents.updated_at,
                    creators.name AS created_by_name
             FROM department_documents
             INNER JOIN users AS creators ON creators.id = department_documents.created_by
             WHERE department_documents.department_id = :department_id
             ORDER BY department_documents.folder_name, department_documents.title'
        );
        $statement->execute(['department_id' => $departmentId]);

        return $statement->fetchAll() ?: [];
    }

    public static function create(array $payload): void
    {
        $statement = self::pdo()->prepare(
            'INSERT INTO department_documents (
                department_id,
                folder_name,
                title,
                body,
                created_by,
                updated_by
            ) VALUES (
                :department_id,
                :folder_name,
                :title,
                :body,
                :created_by,
                :updated_by
            )'
        );
        $statement->execute($payload);
    }

    private static function pdo(): PDO
    {
        return Database::instance()->pdo();
    }
}
