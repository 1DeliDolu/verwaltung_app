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

    public function selectableDepartments(array $user): array
    {
        return Department::allVisibleForUser((int) $user['id'], $this->isAdmin($user));
    }

    public function createEvent(array $user, array $input): void
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

        $eventId = CalendarEvent::create([
            'title' => $title,
            'description' => $description,
            'location' => $location === '' ? null : $location,
            'starts_at' => $startsDateTime->format('Y-m-d H:i:s'),
            'ends_at' => $endsDateTime?->format('Y-m-d H:i:s'),
            'created_by' => (int) $user['id'],
        ], $departmentIds);

        $this->notifyDepartments($eventId, $user, $title, $description, $location, $startsDateTime, $endsDateTime, $departmentIds);
    }

    public function markComplete(int $eventId): void
    {
        CalendarEvent::markComplete($eventId);
    }

    private function notifyDepartments(
        int $eventId,
        array $user,
        string $title,
        string $description,
        string $location,
        \DateTimeInterface $startsAt,
        ?\DateTimeInterface $endsAt,
        array $departmentIds
    ): void {
        if ($departmentIds === []) {
            return;
        }

        $recipients = Department::membersForIds($departmentIds, (int) $user['id']);

        if ($recipients === []) {
            return;
        }

        $subject = 'Neuer Termin: ' . $title;
        $body = implode("\n", array_filter([
            'Ein neuer Termin wurde im Kalender eingetragen.',
            'Titel: ' . $title,
            'Start: ' . $startsAt->format('d.m.Y H:i'),
            $endsAt ? 'Ende: ' . $endsAt->format('d.m.Y H:i') : null,
            $location !== '' ? 'Ort: ' . $location : null,
            '',
            $description,
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
}
