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

    public function testWorkerCannotUseForbiddenTaskTransitionAgainstDatabase(): void
    {
        $this->withDatabaseTransaction(function (): void {
            $itLeader = $this->userByEmail('leiter.it@verwaltung.local');
            $itEmployee = $this->userByEmail('mitarbeiter.it@verwaltung.local');
            $taskService = new TaskService(testApp());
            $itDepartment = array_values(array_filter(
                $taskService->visibleDepartments($itLeader),
                static fn (array $department): bool => (string) ($department['slug'] ?? '') === 'it'
            ))[0] ?? null;

            if ($itDepartment === null) {
                throw new RuntimeException('IT department is required for negative task integration test.');
            }

            $taskId = $taskService->createTask($itLeader, [
                'department_id' => (int) $itDepartment['id'],
                'title' => 'Worker Transition Guard',
                'description' => 'Assigned worker should not reopen done tasks.',
                'priority' => 'normal',
                'due_date' => '',
                'assigned_to_user_id' => (int) $itEmployee['id'],
            ]);

            $task = $taskService->findTask($itLeader, $taskId);

            if ($task === null) {
                throw new RuntimeException('Task should exist for negative task integration test.');
            }

            $taskService->updateStatus($itLeader, $task, 'done');
            $doneTask = $taskService->findTask($itEmployee, $taskId);

            if ($doneTask === null) {
                throw new RuntimeException('Assigned employee should still see the task.');
            }

            $this->expectException(static function () use ($taskService, $itEmployee, $doneTask): void {
                $taskService->updateStatus($itEmployee, $doneTask, 'open');
            });
        });
    }

    public function testForeignLeaderCannotCompleteAnotherDepartmentsEvent(): void
    {
        $this->withDatabaseTransaction(function (\PDO $pdo): void {
            $itLeader = $this->userByEmail('leiter.it@verwaltung.local');
            $hrLeader = $this->userByEmail('leiter.hr@verwaltung.local');
            $calendarService = new CalendarService(testApp());

            $pdo->prepare(
                'INSERT INTO calendar_events (title, description, location, starts_at, ends_at, created_by)
                 VALUES (:title, :description, :location, :starts_at, :ends_at, :created_by)'
            )->execute([
                'title' => 'IT Completion Guard',
                'description' => 'Only creator or admin may complete.',
                'location' => 'Room 2',
                'starts_at' => '2030-02-01 10:00:00',
                'ends_at' => '2030-02-01 11:00:00',
                'created_by' => (int) $itLeader['id'],
            ]);

            $eventId = (int) $pdo->lastInsertId();

            $this->expectException(static function () use ($calendarService, $hrLeader, $eventId): void {
                $calendarService->completeEvent($eventId, $hrLeader);
            });
        });
    }

    public function testNonAdminCannotResetLeaderPasswordAndForeignUserCannotRestoreMail(): void
    {
        $this->withDatabaseTransaction(function (\PDO $pdo): void {
            $itLeader = $this->userByEmail('leiter.it@verwaltung.local');
            $hrLeader = $this->userByEmail('leiter.hr@verwaltung.local');
            $itEmployee = $this->userByEmail('mitarbeiter.it@verwaltung.local');
            $mailService = new InternalMailService(testApp());
            $userService = new UserService(testApp());

            $pdo->prepare(
                'INSERT INTO internal_mails (sender_id, sender_name, sender_email, subject, body)
                 VALUES (:sender_id, :sender_name, :sender_email, :subject, :body)'
            )->execute([
                'sender_id' => (int) $itLeader['id'],
                'sender_name' => (string) $itLeader['name'],
                'sender_email' => (string) $itLeader['email'],
                'subject' => 'Foreign Restore Guard',
                'body' => 'Only sender or recipient may restore.',
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

            $this->assertSame(false, $mailService->restoreMessage($hrLeader, $mailId), 'Foreign leader must not restore unrelated mail.');

            $this->expectException(static function () use ($userService, $hrLeader, $itLeader): void {
                $userService->resetDepartmentLeaderPassword($hrLeader, (int) $itLeader['id']);
            });
        });
    }

    public function testAdminCanChangeLeaderDepartmentAndRoleAssignment(): void
    {
        $this->withDatabaseTransaction(function (\PDO $pdo): void {
            $admin = $this->userByEmail('admin@verwaltung.local');
            $itLeader = $this->userByEmail('leiter.it@verwaltung.local');
            $userService = new UserService(testApp());
            $hrDepartment = array_values(array_filter(
                $userService->assignableDepartments(),
                static fn (array $department): bool => (string) ($department['slug'] ?? '') === 'hr'
            ))[0] ?? null;

            if ($hrDepartment === null) {
                throw new RuntimeException('HR department is required for leader assignment test.');
            }

            $userService->updateDepartmentLeaderAssignment(
                $admin,
                (int) $itLeader['id'],
                (int) $hrDepartment['id'],
                'employee'
            );

            $membershipStatement = $pdo->prepare(
                'SELECT COUNT(*) AS membership_count, MAX(department_id) AS department_id, MAX(membership_role) AS membership_role
                 FROM department_user
                 WHERE user_id = :user_id'
            );
            $membershipStatement->execute(['user_id' => (int) $itLeader['id']]);
            $membershipRow = $membershipStatement->fetch() ?: [];

            $roleStatement = $pdo->prepare(
                'SELECT roles.name AS role_name
                 FROM users
                 INNER JOIN roles ON roles.id = users.role_id
                 WHERE users.id = :id'
            );
            $roleStatement->execute(['id' => (int) $itLeader['id']]);
            $roleRow = $roleStatement->fetch() ?: [];

            $this->assertSame(1, (int) ($membershipRow['membership_count'] ?? 0), 'Exactly one membership should remain after reassignment.');
            $this->assertSame((int) $hrDepartment['id'], (int) ($membershipRow['department_id'] ?? 0), 'Leader should be moved to the selected department.');
            $this->assertSame('employee', (string) ($membershipRow['membership_role'] ?? ''), 'Membership role should be updated.');
            $this->assertSame('employee', (string) ($roleRow['role_name'] ?? ''), 'User role should stay in sync with membership role.');
        });
    }

    public function testAdminLeaderDirectorySupportsSearchAndRoleFilters(): void
    {
        $this->withDatabaseTransaction(function (): void {
            $userService = new UserService(testApp());

            $hrFiltered = $userService->departmentLeaderDirectory([
                'search' => 'hanna',
                'department' => 'hr',
                'membership_role' => 'team_leader',
            ]);

            $employeeFiltered = $userService->departmentLeaderDirectory([
                'membership_role' => 'employee',
            ]);

            $this->assertSame(1, count($hrFiltered), 'Search + department + role filter should narrow to one HR leader.');
            $this->assertSame('leiter.hr@verwaltung.local', (string) ($hrFiltered[0]['email'] ?? ''));
            $this->assertSame(0, count($employeeFiltered), 'Seed leader directory should not return employee membership rows before reassignment.');
        });
    }
}
