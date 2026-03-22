<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Models\CalendarEvent;
use App\Models\Department;
use App\Models\InternalMail;
use App\Models\User;
use RuntimeException;

final class CalendarService
{
    public function __construct(private readonly App $app)
    {
    }

    public function currentUser(): ?array
    {
        $authUser = $this->app->session()->get((string) $this->app->config('auth.session_key', 'auth_user'));
        $userId = (int) ($authUser['id'] ?? 0);

        if ($userId <= 0) {
            return null;
        }

        return User::findById($userId);
    }

    public function upcomingEvents(): array
    {
        return CalendarEvent::upcoming();
    }

    public function editableEvent(int $eventId, array $user): ?array
    {
        $event = CalendarEvent::findActiveById($eventId);

        if ($event === null) {
            return null;
        }

        if (!$this->mayManageEvent($user, $event)) {
            throw new RuntimeException('Not allowed to edit this event.');
        }

        return $event;
    }

    public function selectableDepartments(array $user): array
    {
        return Department::allVisibleForUser((int) $user['id'], $this->isAdmin($user));
    }

    public function createEvent(array $user, array $input): void
    {
        $normalized = $this->normalizeEventInput($user, $input);

        $eventId = CalendarEvent::create([
            'title' => $normalized['title'],
            'description' => $normalized['description'],
            'location' => $normalized['location'],
            'starts_at' => $normalized['starts_at']->format('Y-m-d H:i:s'),
            'ends_at' => $normalized['ends_at']?->format('Y-m-d H:i:s'),
            'created_by' => (int) $user['id'],
        ], $normalized['department_ids']);

        $this->notifyDepartments($user, $normalized, $normalized['department_ids'], 'Neuer Termin: ');
    }

    public function updateEvent(int $eventId, array $user, array $input): void
    {
        $event = $this->editableEvent($eventId, $user);

        if ($event === null) {
            throw new RuntimeException('Event not found.');
        }

        $normalized = $this->normalizeEventInput($user, $input);

        CalendarEvent::update($eventId, [
            'title' => $normalized['title'],
            'description' => $normalized['description'],
            'location' => $normalized['location'],
            'starts_at' => $normalized['starts_at']->format('Y-m-d H:i:s'),
            'ends_at' => $normalized['ends_at']?->format('Y-m-d H:i:s'),
        ], $normalized['department_ids']);

        $this->notifyDepartments($user, $normalized, $normalized['department_ids'], 'Termin aktualisiert: ');
    }

    public function markComplete(int $eventId): void
    {
        CalendarEvent::markComplete($eventId);
    }

    public function deleteEvent(int $eventId, array $user): void
    {
        $event = $this->editableEvent($eventId, $user);

        if ($event === null) {
            throw new RuntimeException('Event not found.');
        }

        CalendarEvent::delete($eventId);
    }

    private function notifyDepartments(array $user, array $event, array $departmentIds, string $subjectPrefix): void
    {
        if ($departmentIds === []) {
            return;
        }

        $recipients = Department::membersForIds($departmentIds, (int) $user['id']);

        if ($recipients === []) {
            return;
        }

        $subject = $subjectPrefix . $event['title'];
        $body = implode("\n", array_filter([
            'Ein Termin wurde im Kalender gespeichert.',
            'Titel: ' . $event['title'],
            'Start: ' . $event['starts_at']->format('d.m.Y H:i'),
            $event['ends_at'] ? 'Ende: ' . $event['ends_at']->format('d.m.Y H:i') : null,
            $event['location_label'] !== '' ? 'Ort: ' . $event['location_label'] : null,
            '',
            $event['description'],
        ]));

        (new MailService($this->app))->sendMessage(
            array_map(static fn (array $recipient): string => (string) $recipient['email'], $recipients),
            $subject,
            $body,
            (string) $user['email'],
            (string) $user['name']
        );

        InternalMail::create([
            'sender_id' => (int) $user['id'],
            'sender_name' => (string) $user['name'],
            'sender_email' => (string) $user['email'],
            'subject' => $subject,
            'body' => $body,
        ], $recipients, []);
    }

    private function isAdmin(array $user): bool
    {
        return ($user['role_name'] ?? null) === 'admin';
    }

    private function mayManageEvent(array $user, array $event): bool
    {
        return $this->isAdmin($user) || (int) $event['created_by'] === (int) $user['id'];
    }

    private function normalizeEventInput(array $user, array $input): array
    {
        $title = trim((string) ($input['title'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $location = trim((string) ($input['location'] ?? ''));
        $startsAt = trim((string) ($input['starts_at'] ?? ''));
        $endsAt = trim((string) ($input['ends_at'] ?? ''));
        $departmentIds = array_values(array_unique(array_filter(array_map(
            static fn (mixed $value): int => (int) $value,
            (array) ($input['department_ids'] ?? [])
        ))));

        if ($title === '' || $description === '' || $startsAt === '') {
            throw new RuntimeException('All event fields are required.');
        }

        $startsDateTime = date_create($startsAt);
        $endsDateTime = $endsAt !== '' ? date_create($endsAt) : null;

        if ($startsDateTime === false || ($endsAt !== '' && $endsDateTime === false)) {
            throw new RuntimeException('Event dates are invalid.');
        }

        if ($endsDateTime !== null && $endsDateTime < $startsDateTime) {
            throw new RuntimeException('End time must be after start time.');
        }

        $visibleDepartments = $this->selectableDepartments($user);
        $allowedDepartmentIds = array_map(static fn (array $department): int => (int) $department['id'], $visibleDepartments);

        foreach ($departmentIds as $departmentId) {
            if (!in_array($departmentId, $allowedDepartmentIds, true)) {
                throw new RuntimeException('Department selection is invalid.');
            }
        }

        return [
            'title' => $title,
            'description' => $description,
            'location' => $location === '' ? null : $location,
            'location_label' => $location,
            'starts_at' => $startsDateTime,
            'ends_at' => $endsDateTime,
            'department_ids' => $departmentIds,
        ];
    }
}
