<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Core\Database;
use App\Models\Department;
use App\Models\User;
use PDOException;
use RuntimeException;

final class UserService
{
    public const DEFAULT_DEPARTMENT_LEADER_PASSWORD = 'DockerDocker!123';

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

    public function departmentLeaderDirectory(array $filters = []): array
    {
        $leaders = array_values(array_filter(
            User::internalDirectory(),
            static function (array $entry): bool {
                return str_starts_with((string) ($entry['email'] ?? ''), 'leiter.');
            }
        ));

        $search = trim((string) ($filters['search'] ?? ''));
        $departmentSlug = trim((string) ($filters['department'] ?? ''));
        $membershipRole = trim((string) ($filters['membership_role'] ?? ''));

        if ($search !== '') {
            $searchNeedle = mb_strtolower($search);
            $leaders = array_values(array_filter($leaders, static function (array $entry) use ($searchNeedle): bool {
                $haystack = mb_strtolower(implode(' ', array_filter([
                    (string) ($entry['name'] ?? ''),
                    (string) ($entry['email'] ?? ''),
                    (string) ($entry['department_name'] ?? ''),
                    (string) ($entry['department_slug'] ?? ''),
                ])));

                return str_contains($haystack, $searchNeedle);
            }));
        }

        if ($departmentSlug !== '') {
            $leaders = array_values(array_filter($leaders, static function (array $entry) use ($departmentSlug): bool {
                return (string) ($entry['department_slug'] ?? '') === $departmentSlug;
            }));
        }

        if ($membershipRole !== '') {
            $leaders = array_values(array_filter($leaders, static function (array $entry) use ($membershipRole): bool {
                return (string) ($entry['membership_role'] ?? '') === $membershipRole;
            }));
        }

        usort($leaders, static function (array $left, array $right): int {
            return [(string) ($left['department_name'] ?? ''), (string) ($left['name'] ?? '')]
                <=>
                [(string) ($right['department_name'] ?? ''), (string) ($right['name'] ?? '')];
        });

        return $leaders;
    }

    public function assignableDepartments(): array
    {
        return Department::all();
    }

    public function membershipRoleOptions(): array
    {
        return [
            'team_leader' => 'Team Lead',
            'employee' => 'Employee',
        ];
    }

    public function resetDepartmentLeaderPassword(array $actor, int $targetUserId): void
    {
        if (!$this->isAdmin($actor)) {
            throw new RuntimeException('Not allowed to reset passwords.');
        }

        $targetLeader = null;

        foreach ($this->departmentLeaderDirectory() as $leader) {
            if ((int) ($leader['id'] ?? 0) === $targetUserId) {
                $targetLeader = $leader;
                break;
            }
        }

        if ($targetLeader === null) {
            throw new RuntimeException('Leader account could not be found.');
        }

        User::resetPasswordByAdmin(
            $targetUserId,
            password_hash(self::DEFAULT_DEPARTMENT_LEADER_PASSWORD, PASSWORD_DEFAULT)
        );
    }

    public function updateDepartmentLeaderAssignment(array $actor, int $targetUserId, int $departmentId, string $membershipRole): void
    {
        if (!$this->isAdmin($actor)) {
            throw new RuntimeException('Not allowed to update leader assignments.');
        }

        if (!array_key_exists($membershipRole, $this->membershipRoleOptions())) {
            throw new RuntimeException('Membership role is invalid.');
        }

        $department = Department::findById($departmentId);

        if ($department === null) {
            throw new RuntimeException('Department could not be found.');
        }

        $targetLeader = null;

        foreach ($this->departmentLeaderDirectory() as $leader) {
            if ((int) ($leader['id'] ?? 0) === $targetUserId) {
                $targetLeader = $leader;
                break;
            }
        }

        if ($targetLeader === null) {
            throw new RuntimeException('Leader account could not be found.');
        }

        $pdo = Database::instance()->pdo();
        $startedTransaction = !$pdo->inTransaction();

        try {
            if ($startedTransaction) {
                $pdo->beginTransaction();
            }

            User::replaceDepartmentMembership($targetUserId, $departmentId, $membershipRole);
            User::updateRoleByName($targetUserId, $membershipRole === 'team_leader' ? 'team_leader' : 'employee');

            if ($startedTransaction) {
                $pdo->commit();
            }
        } catch (PDOException $exception) {
            if ($startedTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw new RuntimeException('Leader assignment could not be updated.', 0, $exception);
        }
    }
}
