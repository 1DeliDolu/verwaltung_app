<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class CalendarEvent
{
    public static function create(array $payload, array $departmentIds): int
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            'INSERT INTO calendar_events (
                title,
                description,
                location,
                starts_at,
                ends_at,
                created_by
            ) VALUES (
                :title,
                :description,
                :location,
                :starts_at,
                :ends_at,
                :created_by
            )'
        );
        $statement->execute([
            'title' => $payload['title'],
            'description' => $payload['description'],
            'location' => $payload['location'],
            'starts_at' => $payload['starts_at'],
            'ends_at' => $payload['ends_at'],
            'created_by' => $payload['created_by'],
        ]);

        $eventId = (int) $pdo->lastInsertId();

        if ($departmentIds !== []) {
            $departmentStatement = $pdo->prepare(
                'INSERT INTO calendar_event_departments (
                    calendar_event_id,
                    department_id
                ) VALUES (
                    :calendar_event_id,
                    :department_id
                )'
            );

            foreach ($departmentIds as $departmentId) {
                $departmentStatement->execute([
                    'calendar_event_id' => $eventId,
                    'department_id' => $departmentId,
                ]);
            }
        }

        $pdo->commit();

        return $eventId;
    }

    public static function upcoming(): array
    {
        $statement = self::pdo()->query(
            'SELECT calendar_events.id,
                    calendar_events.title,
                    calendar_events.description,
                    calendar_events.location,
                    calendar_events.starts_at,
                    calendar_events.ends_at,
                    calendar_events.created_at,
                    creators.name AS created_by_name,
                    GROUP_CONCAT(DISTINCT departments.name ORDER BY departments.name SEPARATOR \', \') AS department_names
             FROM calendar_events
             INNER JOIN users AS creators ON creators.id = calendar_events.created_by
             LEFT JOIN calendar_event_departments
                 ON calendar_event_departments.calendar_event_id = calendar_events.id
             LEFT JOIN departments
                 ON departments.id = calendar_event_departments.department_id
             GROUP BY calendar_events.id, calendar_events.title, calendar_events.description, calendar_events.location, calendar_events.starts_at, calendar_events.ends_at, calendar_events.created_at, creators.name
             ORDER BY calendar_events.starts_at ASC, calendar_events.id ASC'
        );

        return $statement->fetchAll() ?: [];
    }

    private static function pdo(): PDO
    {
        return Database::instance()->pdo();
    }
}
