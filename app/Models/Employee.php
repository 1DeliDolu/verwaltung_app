<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Employee
{
    public static function forDepartment(int $departmentId): array
    {
        $statement = self::pdo()->prepare(
            'SELECT employees.id,
                    employees.department_id,
                    employees.user_id,
                    employees.full_name,
                    employees.employee_number,
                    employees.email,
                    employees.position_title,
                    employees.employment_status,
                    employees.hired_at,
                    employees.personnel_rights,
                    employees.notes,
                    employees.data_processing_basis,
                    employees.retention_until,
                    employees.created_at,
                    employees.updated_at,
                    creators.name AS created_by_name,
                    users.name AS linked_user_name,
                    users.email AS linked_user_email,
                    departments.name AS linked_department_name,
                    departments.slug AS linked_department_slug,
                    department_user.membership_role AS linked_membership_role
             FROM employees
             INNER JOIN users AS creators ON creators.id = employees.created_by
             LEFT JOIN users ON users.id = employees.user_id
             LEFT JOIN department_user ON department_user.user_id = users.id
             LEFT JOIN departments ON departments.id = department_user.department_id
             WHERE employees.department_id = :department_id
             ORDER BY employees.full_name'
        );
        $statement->execute(['department_id' => $departmentId]);

        return $statement->fetchAll() ?: [];
    }

    public static function create(array $payload): int
    {
        $statement = self::pdo()->prepare(
            'INSERT INTO employees (
                department_id,
                user_id,
                full_name,
                employee_number,
                email,
                position_title,
                employment_status,
                hired_at,
                personnel_rights,
                notes,
                data_processing_basis,
                retention_until,
                created_by,
                updated_by
            ) VALUES (
                :department_id,
                :user_id,
                :full_name,
                :employee_number,
                :email,
                :position_title,
                :employment_status,
                :hired_at,
                :personnel_rights,
                :notes,
                :data_processing_basis,
                :retention_until,
                :created_by,
                :updated_by
            )'
        );
        $statement->execute($payload);

        return (int) self::pdo()->lastInsertId();
    }

    public static function findForDepartment(int $departmentId, int $employeeId): ?array
    {
        $statement = self::pdo()->prepare(
            'SELECT id,
                    department_id,
                    user_id,
                    full_name,
                    employee_number,
                    email,
                    position_title,
                    employment_status,
                    hired_at,
                    personnel_rights,
                    notes
             FROM employees
             WHERE department_id = :department_id
               AND id = :id
             LIMIT 1'
        );
        $statement->execute([
            'department_id' => $departmentId,
            'id' => $employeeId,
        ]);
        $employee = $statement->fetch();

        return $employee === false ? null : $employee;
    }

    public static function findByUserId(int $userId): ?array
    {
        $statement = self::pdo()->prepare(
            'SELECT id, user_id, employee_number
             FROM employees
             WHERE user_id = :user_id
             LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        $employee = $statement->fetch();

        return $employee === false ? null : $employee;
    }

    public static function nextPersonnelNumber(): string
    {
        $year = date('Y');
        $statement = self::pdo()->prepare(
            'SELECT MAX(CAST(SUBSTRING_INDEX(employee_number, \'-\', -1) AS UNSIGNED)) AS max_sequence
             FROM employees
             WHERE employee_number LIKE :pattern'
        );
        $statement->execute(['pattern' => 'PN-' . $year . '-%']);
        $row = $statement->fetch() ?: [];
        $nextSequence = ((int) ($row['max_sequence'] ?? 0)) + 1;

        return sprintf('PN-%s-%05d', $year, $nextSequence);
    }

    private static function pdo(): PDO
    {
        return Database::instance()->pdo();
    }
}
