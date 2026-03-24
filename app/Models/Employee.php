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
                    employees.full_name,
                    employees.employee_number,
                    employees.email,
                    employees.position_title,
                    employees.employment_status,
                    employees.hired_at,
                    employees.personnel_rights,
                    employees.notes,
                    employees.created_at,
                    employees.updated_at,
                    creators.name AS created_by_name
             FROM employees
             INNER JOIN users AS creators ON creators.id = employees.created_by
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
                full_name,
                employee_number,
                email,
                position_title,
                employment_status,
                hired_at,
                personnel_rights,
                notes,
                created_by,
                updated_by
            ) VALUES (
                :department_id,
                :full_name,
                :employee_number,
                :email,
                :position_title,
                :employment_status,
                :hired_at,
                :personnel_rights,
                :notes,
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

    private static function pdo(): PDO
    {
        return Database::instance()->pdo();
    }
}
