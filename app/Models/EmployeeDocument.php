<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class EmployeeDocument
{
    public static function forDepartment(int $departmentId): array
    {
        $statement = self::pdo()->prepare(
            'SELECT employee_documents.id,
                    employee_documents.employee_id,
                    employee_documents.original_name,
                    employee_documents.stored_name,
                    employee_documents.file_path,
                    employee_documents.mime_type,
                    employee_documents.file_size,
                    employee_documents.created_at,
                    employees.full_name AS employee_name,
                    employees.employee_number,
                    uploaders.name AS uploaded_by_name
             FROM employee_documents
             INNER JOIN employees ON employees.id = employee_documents.employee_id
             INNER JOIN users AS uploaders ON uploaders.id = employee_documents.uploaded_by
             WHERE employees.department_id = :department_id
             ORDER BY employees.full_name, employee_documents.created_at DESC'
        );
        $statement->execute(['department_id' => $departmentId]);

        return $statement->fetchAll() ?: [];
    }

    public static function create(array $payload): int
    {
        $statement = self::pdo()->prepare(
            'INSERT INTO employee_documents (
                employee_id,
                original_name,
                stored_name,
                file_path,
                mime_type,
                file_size,
                uploaded_by
            ) VALUES (
                :employee_id,
                :original_name,
                :stored_name,
                :file_path,
                :mime_type,
                :file_size,
                :uploaded_by
            )'
        );
        $statement->execute($payload);

        return (int) self::pdo()->lastInsertId();
    }

    public static function findForDepartment(int $departmentId, int $employeeId, int $documentId): ?array
    {
        $statement = self::pdo()->prepare(
            'SELECT employee_documents.id,
                    employee_documents.employee_id,
                    employee_documents.original_name,
                    employee_documents.stored_name,
                    employee_documents.file_path,
                    employee_documents.mime_type,
                    employee_documents.file_size,
                    employees.full_name AS employee_name
             FROM employee_documents
             INNER JOIN employees ON employees.id = employee_documents.employee_id
             WHERE employees.department_id = :department_id
               AND employees.id = :employee_id
               AND employee_documents.id = :document_id
             LIMIT 1'
        );
        $statement->execute([
            'department_id' => $departmentId,
            'employee_id' => $employeeId,
            'document_id' => $documentId,
        ]);
        $document = $statement->fetch();

        return $document === false ? null : $document;
    }

    public static function countForDepartment(int $departmentId): int
    {
        $statement = self::pdo()->prepare(
            'SELECT COUNT(*) AS aggregate_count
             FROM employee_documents
             INNER JOIN employees ON employees.id = employee_documents.employee_id
             WHERE employees.department_id = :department_id'
        );
        $statement->execute(['department_id' => $departmentId]);
        $row = $statement->fetch() ?: [];

        return (int) ($row['aggregate_count'] ?? 0);
    }

    public static function deleteForDepartment(int $departmentId, int $employeeId, int $documentId): void
    {
        $statement = self::pdo()->prepare(
            'DELETE employee_documents
             FROM employee_documents
             INNER JOIN employees ON employees.id = employee_documents.employee_id
             WHERE employees.department_id = :department_id
               AND employees.id = :employee_id
               AND employee_documents.id = :document_id'
        );
        $statement->execute([
            'department_id' => $departmentId,
            'employee_id' => $employeeId,
            'document_id' => $documentId,
        ]);
    }

    private static function pdo(): PDO
    {
        return Database::instance()->pdo();
    }
}
