<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Models\Department;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use RuntimeException;

final class TaskService
{
    public function __construct(private readonly App $app)
    {
    }

    public function currentUser(): array
    {
        $authUser = $this->app->session()->get((string) $this->app->config('auth.session_key', 'auth_user'));
        $userId = (int) ($authUser['id'] ?? 0);
        $user = User::findById($userId);

        if ($user === null) {
            throw new RuntimeException('Authenticated user could not be loaded.');
        }

        return $user;
    }

    public function isAdmin(array $user): bool
    {
        return (string) ($user['role_name'] ?? '') === 'admin';
    }

    public function visibleDepartments(array $user): array
    {
        return Department::allVisibleForUser((int) $user['id'], $this->isAdmin($user));
    }

    public function visibleDepartmentIds(array $user): array
    {
        return array_map(
            static fn (array $department): int => (int) $department['id'],
            $this->visibleDepartments($user)
        );
    }

    public function membershipRolesByDepartment(array $user): array
    {
        $roles = [];

        foreach ($this->visibleDepartments($user) as $department) {
            $roles[(int) $department['id']] = (string) ($department['membership_role'] ?? '');
        }

        return $roles;
    }

    public function listTasks(array $user, array $filters = []): array
    {
        return Task::visibleForUser((int) $user['id'], $this->isAdmin($user), $filters);
    }

    public function statusCounts(array $user, array $filters = []): array
    {
        return Task::countByStatusForUser((int) $user['id'], $this->isAdmin($user), $filters);
    }

    public function recentTasks(array $user, array $filters = [], int $limit = 4): array
    {
        $filters['limit'] = $limit;

        return $this->listTasks($user, $filters);
    }

    public function findTask(array $user, int $taskId): ?array
    {
        return Task::findVisibleForUser($taskId, (int) $user['id'], $this->isAdmin($user));
    }

    public function commentsForTask(array $task): array
    {
        return TaskComment::forTask((int) $task['id']);
    }

    public function assignableUsersForDepartment(int $departmentId, int $excludeUserId = 0): array
    {
        return Department::membersForIds([$departmentId], $excludeUserId);
    }

    public function assignableUsersMap(array $user): array
    {
        $map = [];

        foreach ($this->visibleDepartments($user) as $department) {
            $departmentId = (int) $department['id'];
            $map[$departmentId] = $this->assignableUsersForDepartment($departmentId);
        }

        return $map;
    }

    public function createTask(array $user, array $input): int
    {
        $departmentId = (int) ($input['department_id'] ?? 0);
        $title = trim((string) ($input['title'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $priority = trim((string) ($input['priority'] ?? 'normal'));
        $dueDate = trim((string) ($input['due_date'] ?? ''));
        $assignedToUserId = (int) ($input['assigned_to_user_id'] ?? 0);

        $this->assertDepartmentVisible($user, $departmentId);
        self::assertTaskRules($title, $description, $priority, $dueDate);
        $this->assertAssigneeBelongsToDepartment($departmentId, $assignedToUserId);

        return Task::create([
            'department_id' => $departmentId,
            'title' => $title,
            'description' => $description,
            'status' => 'open',
            'priority' => $priority,
            'due_date' => $dueDate === '' ? null : $dueDate,
            'created_by_user_id' => (int) $user['id'],
            'assigned_to_user_id' => $assignedToUserId > 0 ? $assignedToUserId : null,
        ]);
    }

    public function updateTask(array $user, array $task, array $input): void
    {
        if (!$this->mayManageTask($user, $task)) {
            throw new RuntimeException('Not allowed to edit this task.');
        }

        $departmentId = (int) ($input['department_id'] ?? 0);
        $title = trim((string) ($input['title'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $priority = trim((string) ($input['priority'] ?? 'normal'));
        $dueDate = trim((string) ($input['due_date'] ?? ''));
        $assignedToUserId = (int) ($input['assigned_to_user_id'] ?? 0);

        $this->assertDepartmentVisible($user, $departmentId);
        self::assertTaskRules($title, $description, $priority, $dueDate);
        $this->assertAssigneeBelongsToDepartment($departmentId, $assignedToUserId);

        Task::updateTask((int) $task['id'], [
            'department_id' => $departmentId,
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
            'due_date' => $dueDate === '' ? null : $dueDate,
            'assigned_to_user_id' => $assignedToUserId > 0 ? $assignedToUserId : null,
        ]);
    }

    public function updateStatus(array $user, array $task, string $status): void
    {
        if (!$this->mayWorkOnTask($user, $task)) {
            throw new RuntimeException('Not allowed to update this task status.');
        }

        if ($status === (string) $task['status']) {
            return;
        }

        self::assertStatusTransition((string) $task['status'], $status, $this->mayManageTask($user, $task));
        Task::updateStatus((int) $task['id'], $status);
    }

    public function addComment(array $user, array $task, string $body): void
    {
        if (!$this->mayViewTask($user, $task)) {
            throw new RuntimeException('Not allowed to comment on this task.');
        }

        $body = trim($body);

        if ($body === '') {
            throw new RuntimeException('Comment body is required.');
        }

        TaskComment::create((int) $task['id'], (int) $user['id'], $body);
    }

    public function mayViewTask(array $user, array $task): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return in_array((int) $task['department_id'], $this->visibleDepartmentIds($user), true);
    }

    public function mayManageTask(array $user, array $task): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ((int) $task['created_by_user_id'] === (int) $user['id']) {
            return true;
        }

        $roles = $this->membershipRolesByDepartment($user);

        return ($roles[(int) $task['department_id']] ?? '') === 'team_leader';
    }

    public function mayWorkOnTask(array $user, array $task): bool
    {
        if ($this->mayManageTask($user, $task)) {
            return true;
        }

        return (int) ($task['assigned_to_user_id'] ?? 0) === (int) $user['id'];
    }

    public static function statuses(): array
    {
        return [
            'open' => 'Offen',
            'in_progress' => 'In Bearbeitung',
            'blocked' => 'Blockiert',
            'done' => 'Erledigt',
        ];
    }

    public function availableStatuses(array $user, array $task): array
    {
        return self::allowedStatusesForState(
            (string) ($task['status'] ?? ''),
            $this->mayManageTask($user, $task)
        );
    }

    public static function allowedStatusesForState(string $currentStatus, bool $canManage): array
    {
        self::assertTaskStatus($currentStatus);

        $transitionMap = [
            'open' => [
                'worker' => ['in_progress', 'blocked'],
                'manager' => ['in_progress', 'blocked', 'done'],
            ],
            'in_progress' => [
                'worker' => ['blocked', 'done'],
                'manager' => ['open', 'blocked', 'done'],
            ],
            'blocked' => [
                'worker' => ['in_progress'],
                'manager' => ['open', 'in_progress', 'done'],
            ],
            'done' => [
                'worker' => [],
                'manager' => ['open', 'in_progress'],
            ],
        ];

        $actorKey = $canManage ? 'manager' : 'worker';

        return $transitionMap[$currentStatus][$actorKey] ?? [];
    }

    public static function priorities(): array
    {
        return [
            'low' => 'Niedrig',
            'normal' => 'Normal',
            'high' => 'Hoch',
            'urgent' => 'Kritisch',
        ];
    }

    public static function assertTaskStatus(string $status): void
    {
        if (!array_key_exists($status, self::statuses())) {
            throw new RuntimeException('Invalid task status.');
        }
    }

    public static function assertStatusTransition(string $currentStatus, string $nextStatus, bool $canManage): void
    {
        self::assertTaskStatus($nextStatus);

        if (!in_array($nextStatus, self::allowedStatusesForState($currentStatus, $canManage), true)) {
            throw new RuntimeException('Task status transition is not allowed.');
        }
    }

    public static function assertTaskRules(string $title, string $description, string $priority, string $dueDate): void
    {
        if ($title === '' || $description === '') {
            throw new RuntimeException('Title and description are required.');
        }

        if (!array_key_exists($priority, self::priorities())) {
            throw new RuntimeException('Invalid task priority.');
        }

        if ($dueDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
            throw new RuntimeException('Invalid due date.');
        }
    }

    private function assertDepartmentVisible(array $user, int $departmentId): void
    {
        if ($departmentId <= 0 || !in_array($departmentId, $this->visibleDepartmentIds($user), true)) {
            throw new RuntimeException('Selected department is not available.');
        }
    }

    private function assertAssigneeBelongsToDepartment(int $departmentId, int $assignedToUserId): void
    {
        if ($assignedToUserId <= 0) {
            return;
        }

        $assignableIds = array_map(
            static fn (array $entry): int => (int) $entry['id'],
            $this->assignableUsersForDepartment($departmentId)
        );

        if (!in_array($assignedToUserId, $assignableIds, true)) {
            throw new RuntimeException('Selected assignee is not part of the department.');
        }
    }
}
