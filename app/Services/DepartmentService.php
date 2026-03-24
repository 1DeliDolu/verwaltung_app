<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Models\Department;
use App\Models\DepartmentDocument;
use App\Models\Employee;
use App\Models\EmployeeDocument;
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

    public function employeesForDepartment(array $department): array
    {
        if (!$this->isHumanResourcesDepartment($department)) {
            return [];
        }

        $employees = Employee::forDepartment((int) $department['id']);
        $documents = EmployeeDocument::forDepartment((int) $department['id']);
        $documentsByEmployee = [];

        foreach ($documents as $document) {
            $documentsByEmployee[(int) $document['employee_id']][] = $document;
        }

        foreach ($employees as &$employee) {
            $employee['documents'] = $documentsByEmployee[(int) $employee['id']] ?? [];
        }
        unset($employee);

        return $employees;
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

    public function createEmployee(array $department, array $input): void
    {
        if (!$this->isHumanResourcesDepartment($department) || !$this->mayManageDepartment($department)) {
            throw new RuntimeException('Not allowed to manage employees in this department.');
        }

        $fullName = trim((string) ($input['full_name'] ?? ''));
        $employeeNumber = trim((string) ($input['employee_number'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $positionTitle = trim((string) ($input['position_title'] ?? ''));
        $employmentStatus = trim((string) ($input['employment_status'] ?? 'active'));
        $hiredAt = trim((string) ($input['hired_at'] ?? ''));
        $personnelRights = trim((string) ($input['personnel_rights'] ?? ''));
        $notes = trim((string) ($input['notes'] ?? ''));

        if ($fullName === '' || $employeeNumber === '') {
            throw new RuntimeException('Employee name and number are required.');
        }

        if (!in_array($employmentStatus, ['active', 'on_leave', 'inactive'], true)) {
            throw new RuntimeException('Invalid employment status.');
        }

        if ($hiredAt !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hiredAt)) {
            throw new RuntimeException('Invalid hire date.');
        }

        $user = $this->currentUser();

        Employee::create([
            'department_id' => $department['id'],
            'full_name' => $fullName,
            'employee_number' => $employeeNumber,
            'email' => $email === '' ? null : $email,
            'position_title' => $positionTitle === '' ? null : $positionTitle,
            'employment_status' => $employmentStatus,
            'hired_at' => $hiredAt === '' ? null : $hiredAt,
            'personnel_rights' => $personnelRights === '' ? null : $personnelRights,
            'notes' => $notes === '' ? null : $notes,
            'created_by' => $user['id'],
            'updated_by' => $user['id'],
        ]);
    }

    public function createEmployeeDocument(array $department, int $employeeId, array $file): void
    {
        if (!$this->isHumanResourcesDepartment($department) || !$this->mayManageDepartment($department)) {
            throw new RuntimeException('Not allowed to manage employees in this department.');
        }

        $employee = Employee::findForDepartment((int) $department['id'], $employeeId);

        if ($employee === null) {
            throw new RuntimeException('Employee could not be found.');
        }

        $user = $this->currentUser();
        $storedFile = (new FilesystemService($this->app))->storeEmployeeDocument(
            (string) $department['slug'],
            (int) $employee['id'],
            (string) $employee['employee_number'],
            $file
        );

        EmployeeDocument::create([
            'employee_id' => $employee['id'],
            'original_name' => $storedFile['original_name'],
            'stored_name' => $storedFile['stored_name'],
            'file_path' => $storedFile['file_path'],
            'mime_type' => $storedFile['mime_type'],
            'file_size' => $storedFile['file_size'],
            'uploaded_by' => $user['id'],
        ]);
    }

    public function employeeDocumentForDownload(array $department, int $employeeId, int $documentId): ?array
    {
        if (!$this->isHumanResourcesDepartment($department)) {
            return null;
        }

        return EmployeeDocument::findForDepartment((int) $department['id'], $employeeId, $documentId);
    }

    public function isHumanResourcesDepartment(array $department): bool
    {
        return (string) ($department['slug'] ?? '') === 'hr';
    }

    private function isAdmin(array $user): bool
    {
        return ($user['role_name'] ?? null) === 'admin';
    }
}
