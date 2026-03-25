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

    public static function upcomingForUser(int $userId, bool $isAdmin, array $departmentIds): array
    {
        $query = 'SELECT calendar_events.id,
                         calendar_events.title,
                         calendar_events.description,
                         calendar_events.location,
                         calendar_events.starts_at,
                         calendar_events.ends_at,
                         calendar_events.completed_at,
                         calendar_events.created_by,
                         calendar_events.created_at,
                         creators.name AS created_by_name,
                         GROUP_CONCAT(DISTINCT departments.name ORDER BY departments.name SEPARATOR \', \') AS department_names
                  FROM calendar_events
                  INNER JOIN users AS creators ON creators.id = calendar_events.created_by
                  LEFT JOIN calendar_event_departments
                      ON calendar_event_departments.calendar_event_id = calendar_events.id
                  LEFT JOIN departments
                      ON departments.id = calendar_event_departments.department_id
                  WHERE calendar_events.completed_at IS NULL ';

        $params = [];

        if (!$isAdmin) {
            $query .= 'AND (
                            calendar_events.created_by = ?
                            OR NOT EXISTS (
                                SELECT 1
                                FROM calendar_event_departments AS visibility_departments
                                WHERE visibility_departments.calendar_event_id = calendar_events.id
                            )';
            $params[] = $userId;

            if ($departmentIds !== []) {
                $placeholders = implode(', ', array_fill(0, count($departmentIds), '?'));
                $query .= " OR EXISTS (
                                SELECT 1
                                FROM calendar_event_departments AS visibility_departments
                                WHERE visibility_departments.calendar_event_id = calendar_events.id
                                  AND visibility_departments.department_id IN ($placeholders)
                            )";
                array_push($params, ...$departmentIds);
            }

            $query .= ') ';
        }

        $query .= 'GROUP BY calendar_events.id, calendar_events.title, calendar_events.description, calendar_events.location, calendar_events.starts_at, calendar_events.ends_at, calendar_events.completed_at, calendar_events.created_by, calendar_events.created_at, creators.name
                   ORDER BY calendar_events.starts_at ASC, calendar_events.id ASC';

        $statement = self::pdo()->prepare($query);
        $statement->execute($params);

        return $statement->fetchAll() ?: [];
    }

    public static function findActiveById(int $eventId): ?array
    {
        $statement = self::pdo()->prepare(
            'SELECT id, title, description, location, starts_at, ends_at, completed_at, created_by
             FROM calendar_events
             WHERE id = :id AND completed_at IS NULL
             LIMIT 1'
        );
        $statement->execute(['id' => $eventId]);
        $event = $statement->fetch();

        if ($event === false) {
            return null;
        }

        $departmentStatement = self::pdo()->prepare(
            'SELECT department_id
             FROM calendar_event_departments
             WHERE calendar_event_id = :event_id
             ORDER BY department_id'
        );
        $departmentStatement->execute(['event_id' => $eventId]);
        $event['department_ids'] = array_map(
            static fn (array $row): int => (int) $row['department_id'],
            $departmentStatement->fetchAll() ?: []
        );

        return $event;
    }

    public static function update(int $eventId, array $payload, array $departmentIds): void
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            'UPDATE calendar_events
             SET title = :title,
                 description = :description,
                 location = :location,
                 starts_at = :starts_at,
                 ends_at = :ends_at,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $eventId,
            'title' => $payload['title'],
            'description' => $payload['description'],
            'location' => $payload['location'],
            'starts_at' => $payload['starts_at'],
            'ends_at' => $payload['ends_at'],
        ]);

        $deleteStatement = $pdo->prepare(
            'DELETE FROM calendar_event_departments
             WHERE calendar_event_id = :event_id'
        );
        $deleteStatement->execute(['event_id' => $eventId]);

        if ($departmentIds !== []) {
            $insertStatement = $pdo->prepare(
                'INSERT INTO calendar_event_departments (
                    calendar_event_id,
                    department_id
                ) VALUES (
                    :calendar_event_id,
                    :department_id
                )'
            );

            foreach ($departmentIds as $departmentId) {
                $insertStatement->execute([
                    'calendar_event_id' => $eventId,
                    'department_id' => $departmentId,
                ]);
            }
        }

        $pdo->commit();
    }

    public static function markComplete(int $eventId): void
    {
        $statement = self::pdo()->prepare(
            'UPDATE calendar_events
             SET completed_at = NOW()
             WHERE id = :id'
        );
        $statement->execute(['id' => $eventId]);
    }

    public static function delete(int $eventId): void
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();

        $departmentStatement = $pdo->prepare(
            'DELETE FROM calendar_event_departments
             WHERE calendar_event_id = :event_id'
        );
        $departmentStatement->execute(['event_id' => $eventId]);

        $eventStatement = $pdo->prepare(
            'DELETE FROM calendar_events
             WHERE id = :id'
        );
        $eventStatement->execute(['id' => $eventId]);

        $pdo->commit();
    }

    private static function pdo(): PDO
    {
        return Database::instance()->pdo();
    }
}
