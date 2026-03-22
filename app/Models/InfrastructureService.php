<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class InfrastructureService
{
    public static function all(): array
    {
        $statement = self::pdo()->query(
            'SELECT infrastructure_services.id,
                    infrastructure_services.name,
                    infrastructure_services.service_type,
                    infrastructure_services.host_name,
                    infrastructure_services.status,
                    infrastructure_services.access_level,
                    infrastructure_services.description,
                    departments.name AS department_name
             FROM infrastructure_services
             LEFT JOIN departments ON departments.id = infrastructure_services.managed_by_department_id
             ORDER BY infrastructure_services.name'
        );

        return $statement->fetchAll() ?: [];
    }

    private static function pdo(): PDO
    {
        return Database::instance()->pdo();
    }
}
