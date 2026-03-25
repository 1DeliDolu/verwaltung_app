<?php

declare(strict_types=1);

use App\Services\CalendarService;
use App\Services\InternalMailService;
use App\Services\TaskService;
use App\Services\UserService;

final class DatabaseWorkflowTest extends TestCase
{
    public function testTaskVisibilityIsScopedByDepartmentMembership(): void
    {
        $this->withDatabaseTransaction(function (): void {
            $itLeader = $this->userByEmail('leiter.it@verwaltung.local');
            $hrLeader = $this->userByEmail('leiter.hr@verwaltung.local');
            $itEmployee = $this->userByEmail('mitarbeiter.it@verwaltung.local');
            $taskService = new TaskService(testApp());
            $itDepartment = array_values(array_filter(
                $taskService->visibleDepartments($itLeader),
                static fn (array $department): bool => (string) ($department['slug'] ?? '') === 'it'
            ))[0] ?? null;

            if ($itDepartment === null) {
                throw new RuntimeException('IT department is required for task integration test.');
            }

            $taskId = $taskService->createTask($itLeader, [
                'department_id' => (int) $itDepartment['id'],
                'title' => 'Integration Task Visibility',
                'description' => 'Task should only be visible inside IT.',
                'priority' => 'normal',
                'due_date' => '',
                'assigned_to_user_id' => (int) $itEmployee['id'],
            ]);

            $visibleToIt = $taskService->findTask($itLeader, $taskId);
            $visibleToHr = $taskService->findTask($hrLeader, $taskId);

            $this->assertTrue($visibleToIt !== null, 'IT leader should see IT task.');
            $this->assertSame(null, $visibleToHr, 'HR leader must not see IT task.');
        });
    }

    public function testCalendarVisibilityFiltersDepartmentBoundEvents(): void
    {
        $this->withDatabaseTransaction(function (PDO $pdo): void {
            $itLeader = $this->userByEmail('leiter.it@verwaltung.local');
            $hrLeader = $this->userByEmail('leiter.hr@verwaltung.local');
            $calendarService = new CalendarService(testApp());
            $taskService = new TaskService(testApp());
            $itDepartment = array_values(array_filter(
                $taskService->visibleDepartments($itLeader),
                static fn (array $department): bool => (string) ($department['slug'] ?? '') === 'it'
            ))[0] ?? null;

            if ($itDepartment === null) {
                throw new RuntimeException('IT department is required for calendar integration test.');
            }

            $pdo->prepare(
                'INSERT INTO calendar_events (title, description, location, starts_at, ends_at, created_by)
                 VALUES (:title, :description, :location, :starts_at, :ends_at, :created_by)'
            )->execute([
                'title' => 'IT Only Planning',
                'description' => 'Restricted test event.',
                'location' => 'Room 1',
                'starts_at' => '2030-01-02 10:00:00',
                'ends_at' => '2030-01-02 11:00:00',
                'created_by' => (int) $itLeader['id'],
            ]);

            $eventId = (int) $pdo->lastInsertId();

            $pdo->prepare(
                'INSERT INTO calendar_event_departments (calendar_event_id, department_id)
                 VALUES (:calendar_event_id, :department_id)'
            )->execute([
                'calendar_event_id' => $eventId,
                'department_id' => (int) $itDepartment['id'],
            ]);

            $_SESSION['auth_user'] = $itLeader;
            $itEvents = $calendarService->upcomingEvents();

            $_SESSION['auth_user'] = $hrLeader;
            $hrEvents = $calendarService->upcomingEvents();

            $itTitles = array_map(static fn (array $event): string => (string) $event['title'], $itEvents);
            $hrTitles = array_map(static fn (array $event): string => (string) $event['title'], $hrEvents);

            $this->assertTrue(in_array('IT Only Planning', $itTitles, true), 'IT leader should see IT calendar event.');
            $this->assertSame(false, in_array('IT Only Planning', $hrTitles, true), 'HR leader must not see IT-only calendar event.');
        });
    }

    public function testMailArchiveRestoreAndAdminResetWorkAgainstDatabase(): void
    {
        $this->withDatabaseTransaction(function (\PDO $pdo): void {
            $itLeader = $this->userByEmail('leiter.it@verwaltung.local');
            $itEmployee = $this->userByEmail('mitarbeiter.it@verwaltung.local');
            $admin = $this->userByEmail('admin@verwaltung.local');
            $mailService = new InternalMailService(testApp());
            $userService = new UserService(testApp());

            $pdo->prepare(
                'INSERT INTO internal_mails (sender_id, sender_name, sender_email, subject, body)
                 VALUES (:sender_id, :sender_name, :sender_email, :subject, :body)'
            )->execute([
                'sender_id' => (int) $itLeader['id'],
                'sender_name' => (string) $itLeader['name'],
                'sender_email' => (string) $itLeader['email'],
                'subject' => 'Archive Restore Integration',
                'body' => 'Testing archive and restore.',
            ]);
            $mailId = (int) $pdo->lastInsertId();

            $pdo->prepare(
                'INSERT INTO internal_mail_recipients (mail_id, recipient_user_id, recipient_name, recipient_email)
                 VALUES (:mail_id, :recipient_user_id, :recipient_name, :recipient_email)'
            )->execute([
                'mail_id' => $mailId,
                'recipient_user_id' => (int) $itEmployee['id'],
                'recipient_name' => (string) $itEmployee['name'],
                'recipient_email' => (string) $itEmployee['email'],
            ]);

            $this->assertTrue($mailService->archiveMessage($itEmployee, $mailId));
            $this->assertTrue($mailService->restoreMessage($itEmployee, $mailId));

            $userService->resetDepartmentLeaderPassword($admin, (int) $itLeader['id']);

            $statement = $this->pdo()->prepare(
                'SELECT password_change_required_at, password_changed_at
                 FROM users
                 WHERE id = :id'
            );
            $statement->execute(['id' => (int) $itLeader['id']]);
            $row = $statement->fetch() ?: [];

            $this->assertTrue(($row['password_change_required_at'] ?? null) !== null, 'Reset should require password change.');
            $this->assertSame(null, $row['password_changed_at'] ?? null, 'Reset should clear password_changed_at.');
        });
    }
}
