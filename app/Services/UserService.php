<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Models\User;
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

    public function departmentLeaderDirectory(): array
    {
        $leaders = array_values(array_filter(
            User::internalDirectory(),
            static function (array $entry): bool {
                return (string) ($entry['membership_role'] ?? '') === 'team_leader'
                    && str_starts_with((string) ($entry['email'] ?? ''), 'leiter.');
            }
        ));

        usort($leaders, static function (array $left, array $right): int {
            return [(string) ($left['department_name'] ?? ''), (string) ($left['name'] ?? '')]
                <=>
                [(string) ($right['department_name'] ?? ''), (string) ($right['name'] ?? '')];
        });

        return $leaders;
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
}
