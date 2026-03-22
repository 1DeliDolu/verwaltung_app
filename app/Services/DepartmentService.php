<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Models\Department;
use App\Models\DepartmentDocument;
use App\Models\User;
use RuntimeException;

final class DepartmentService
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

    public function listVisibleDepartments(): array
    {
        $user = $this->currentUser();

        return Department::allVisibleForUser($user['id'], $this->isAdmin($user));
    }

    public function findVisibleDepartment(string $slug): ?array
    {
        $user = $this->currentUser();

        return Department::findVisibleForUser($slug, $user['id'], $this->isAdmin($user));
    }

    public function documentsForDepartment(int $departmentId): array
    {
        return DepartmentDocument::forDepartment($departmentId);
    }

    public function mayManageDepartment(array $department): bool
    {
        $user = $this->currentUser();

        if ($this->isAdmin($user)) {
            return true;
        }

        return ($department['membership_role'] ?? null) === 'team_leader';
    }

    public function createDocument(array $department, array $input): void
    {
        if (!$this->mayManageDepartment($department)) {
            throw new RuntimeException('Not allowed to manage this department.');
        }

        $folderName = trim((string) ($input['folder_name'] ?? ''));
        $title = trim((string) ($input['title'] ?? ''));
        $body = trim((string) ($input['body'] ?? ''));

        if ($folderName === '' || $title === '' || $body === '') {
            throw new RuntimeException('All document fields are required.');
        }

        $user = $this->currentUser();

        DepartmentDocument::create([
            'department_id' => $department['id'],
            'folder_name' => $folderName,
            'title' => $title,
            'body' => $body,
            'created_by' => $user['id'],
            'updated_by' => $user['id'],
        ]);
    }

    private function isAdmin(array $user): bool
    {
        return ($user['role_name'] ?? null) === 'admin';
    }
}
