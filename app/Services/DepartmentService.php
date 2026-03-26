<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Core\Database;
use App\Models\Department;
use App\Models\DepartmentDocument;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use PDOException;
use RuntimeException;

final class DepartmentService
{
    public function __construct(private readonly App $app)
    {
    }

    public function app(): App
    {
        return $this->app;
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

        return array_map(
            fn (array $department): array => $this->enrichDepartment($department),
            Department::allVisibleForUser($user['id'], $this->isAdmin($user))
        );
    }

    public function listVisibleDepartmentsWithSummaryStats(): array
    {
        return array_map(
            fn (array $department): array => $this->withSummaryStats($department),
            $this->listVisibleDepartments()
        );
    }

    public function dashboardDepartments(): array
    {
        $departments = $this->listVisibleDepartments();

        foreach ($departments as &$department) {
            $department = $this->withSummaryStats($department);
            $department['can_manage'] = $this->mayManageDepartment($department);
            $department['quick_actions'] = $this->quickActionsForDepartment($department);
        }
        unset($department);

        return $departments;
    }

    public function findVisibleDepartment(string $slug): ?array
    {
        $user = $this->currentUser();

        $department = Department::findVisibleForUser($slug, $user['id'], $this->isAdmin($user));

        if ($department === null) {
            return null;
        }

        return $this->enrichDepartment($department);
    }

    public function findVisibleDepartmentWithSummaryStats(string $slug): ?array
    {
        $department = $this->findVisibleDepartment($slug);

        if ($department === null) {
            return null;
        }

        return $this->withSummaryStats($department);
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

    public function assignableDepartments(array $department): array
    {
        if (!$this->isInformationTechnologyDepartment($department) || !$this->mayManageDepartment($department)) {
            return [];
        }

        return Department::all();
    }

    public function eligiblePersonnelUsers(array $department): array
    {
        if (!$this->isHumanResourcesDepartment($department)) {
            return [];
        }

        return User::eligibleForPersonnelProfiles();
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

        $userId = (int) ($input['user_id'] ?? 0);
        $positionTitle = trim((string) ($input['position_title'] ?? ''));
        $employmentStatus = trim((string) ($input['employment_status'] ?? 'active'));
        $hiredAt = trim((string) ($input['hired_at'] ?? ''));
        $personnelRights = trim((string) ($input['personnel_rights'] ?? ''));
        $notes = trim((string) ($input['notes'] ?? ''));
        $dataProcessingBasis = trim((string) ($input['data_processing_basis'] ?? ''));
        $retentionUntil = trim((string) ($input['retention_until'] ?? ''));

        if ($userId <= 0) {
            throw new RuntimeException('A managed user must be selected.');
        }
        self::assertEmployeeProfileRules($employmentStatus, $hiredAt, $dataProcessingBasis, $retentionUntil);

        $linkedUser = User::findById($userId);

        if ($linkedUser === null || ($linkedUser['created_by_user_id'] ?? null) === null) {
            throw new RuntimeException('Selected user is not eligible for HR profiling.');
        }

        if (Employee::findByUserId($userId) !== null) {
            throw new RuntimeException('A personnel profile already exists for this user.');
        }

        $user = $this->currentUser();
        $employeeNumber = Employee::nextPersonnelNumber();

        Employee::create([
            'department_id' => $department['id'],
            'user_id' => $linkedUser['id'],
            'full_name' => $linkedUser['name'],
            'employee_number' => $employeeNumber,
            'email' => $linkedUser['email'] === '' ? null : $linkedUser['email'],
            'position_title' => $positionTitle === '' ? null : $positionTitle,
            'employment_status' => $employmentStatus,
            'hired_at' => $hiredAt === '' ? null : $hiredAt,
            'personnel_rights' => $personnelRights === '' ? null : $personnelRights,
            'notes' => $notes === '' ? null : $notes,
            'data_processing_basis' => $dataProcessingBasis,
            'retention_until' => $retentionUntil === '' ? null : $retentionUntil,
            'created_by' => $user['id'],
            'updated_by' => $user['id'],
        ]);
    }

    public function createManagedPerson(array $department, array $input): void
    {
        if (!$this->isInformationTechnologyDepartment($department) || !$this->mayManageDepartment($department)) {
            throw new RuntimeException('Not allowed to provision managed people in this department.');
        }

        $name = trim((string) ($input['name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $temporaryPassword = (string) ($input['temporary_password'] ?? '');
        $temporaryPasswordConfirmation = (string) ($input['temporary_password_confirmation'] ?? '');
        $targetDepartmentId = (int) ($input['target_department_id'] ?? 0);
        $membershipRole = trim((string) ($input['membership_role'] ?? 'employee'));
        self::assertManagedPersonRules($name, $email, $temporaryPassword, $temporaryPasswordConfirmation, $membershipRole);

        $targetDepartment = Department::findById($targetDepartmentId);

        if ($targetDepartment === null) {
            throw new RuntimeException('Target department could not be found.');
        }

        (new AuthService($this->app))->assertPasswordStrength($temporaryPassword, $email, $name);

        $actor = $this->currentUser();
        $pdo = Database::instance()->pdo();

        try {
            $pdo->beginTransaction();

            $userId = User::createProvisionedAccount([
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash($temporaryPassword, PASSWORD_DEFAULT),
                'role_name' => $membershipRole === 'team_leader' ? 'team_leader' : 'employee',
                'created_by_user_id' => $actor['id'],
            ]);

            User::addDepartmentMembership($userId, (int) $targetDepartment['id'], $membershipRole);

            $pdo->commit();
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw new RuntimeException('Managed person could not be provisioned.', 0, $exception);
        }
    }

    public function createEmployeeDocument(array $department, int $employeeId, array $file): array
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

        $documentId = EmployeeDocument::create([
            'employee_id' => $employee['id'],
            'original_name' => $storedFile['original_name'],
            'stored_name' => $storedFile['stored_name'],
            'file_path' => $storedFile['file_path'],
            'mime_type' => $storedFile['mime_type'],
            'file_size' => $storedFile['file_size'],
            'uploaded_by' => $user['id'],
        ]);

        $document = EmployeeDocument::findForDepartment((int) $department['id'], $employeeId, $documentId);

        if ($document === null) {
            throw new RuntimeException('Stored employee document could not be loaded.');
        }

        return $document;
    }

    public function updateEmployee(array $department, int $employeeId, array $input): void
    {
        if (!$this->isHumanResourcesDepartment($department) || !$this->mayManageDepartment($department)) {
            throw new RuntimeException('Not allowed to manage employees in this department.');
        }

        $employee = Employee::findForDepartment((int) $department['id'], $employeeId);

        if ($employee === null) {
            throw new RuntimeException('Employee could not be found.');
        }

        $employmentStatus = trim((string) ($input['employment_status'] ?? 'active'));
        $hiredAt = trim((string) ($input['hired_at'] ?? ''));
        $retentionUntil = trim((string) ($input['retention_until'] ?? ''));
        $dataProcessingBasis = trim((string) ($input['data_processing_basis'] ?? ''));
        self::assertEmployeeProfileRules($employmentStatus, $hiredAt, $dataProcessingBasis, $retentionUntil);

        $user = $this->currentUser();

        Employee::updateForDepartment([
            'id' => $employeeId,
            'department_id' => $department['id'],
            'position_title' => trim((string) ($input['position_title'] ?? '')) ?: null,
            'employment_status' => $employmentStatus,
            'hired_at' => $hiredAt === '' ? null : $hiredAt,
            'personnel_rights' => trim((string) ($input['personnel_rights'] ?? '')) ?: null,
            'notes' => trim((string) ($input['notes'] ?? '')) ?: null,
            'data_processing_basis' => $dataProcessingBasis,
            'retention_until' => $retentionUntil === '' ? null : $retentionUntil,
            'updated_by' => $user['id'],
        ]);
    }

    public function deleteEmployee(array $department, int $employeeId): void
    {
        if (!$this->isHumanResourcesDepartment($department) || !$this->mayManageDepartment($department)) {
            throw new RuntimeException('Not allowed to manage employees in this department.');
        }

        $employee = Employee::findForDepartment((int) $department['id'], $employeeId);

        if ($employee === null) {
            throw new RuntimeException('Employee could not be found.');
        }

        $documents = EmployeeDocument::forDepartment((int) $department['id']);
        $filesystem = new FilesystemService($this->app);

        foreach ($documents as $document) {
            if ((int) $document['employee_id'] !== $employeeId) {
                continue;
            }

            try {
                $filesystem->deleteDepartmentFile((string) $department['slug'], (string) $document['file_path']);
            } catch (\RuntimeException $exception) {
                // Ignore missing files and continue with DB cleanup.
            }
        }

        Employee::deleteForDepartment((int) $department['id'], $employeeId);
        $filesystem->deleteEmployeeDirectory((string) $department['slug'], (string) $employee['employee_number']);
    }

    public function deleteEmployeeDocument(array $department, int $employeeId, int $documentId): array
    {
        if (!$this->isHumanResourcesDepartment($department) || !$this->mayManageDepartment($department)) {
            throw new RuntimeException('Not allowed to manage employees in this department.');
        }

        $document = EmployeeDocument::findForDepartment((int) $department['id'], $employeeId, $documentId);

        if ($document === null) {
            throw new RuntimeException('Employee document could not be found.');
        }

        try {
            (new FilesystemService($this->app))->deleteDepartmentFile(
                (string) $department['slug'],
                (string) $document['file_path']
            );
        } catch (\RuntimeException $exception) {
            // Ignore missing physical files and ensure the DB record is still cleaned up.
        }

        EmployeeDocument::deleteForDepartment((int) $department['id'], $employeeId, $documentId);

        return $document;
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

    public function isInformationTechnologyDepartment(array $department): bool
    {
        return (string) ($department['slug'] ?? '') === 'it';
    }

    public static function assertManagedPersonRules(
        string $name,
        string $email,
        string $temporaryPassword,
        string $temporaryPasswordConfirmation,
        string $membershipRole
    ): void {
        if ($name === '' || $email === '' || $temporaryPassword === '' || $temporaryPasswordConfirmation === '') {
            throw new RuntimeException('All managed person fields are required.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Invalid email address.');
        }

        if ($temporaryPassword !== $temporaryPasswordConfirmation) {
            throw new RuntimeException('Temporary password confirmation does not match.');
        }

        if (!in_array($membershipRole, ['employee', 'team_leader'], true)) {
            throw new RuntimeException('Invalid membership role.');
        }
    }

    public static function assertEmployeeProfileRules(
        string $employmentStatus,
        string $hiredAt,
        string $dataProcessingBasis,
        string $retentionUntil
    ): void {
        if (!in_array($employmentStatus, ['active', 'on_leave', 'inactive'], true)) {
            throw new RuntimeException('Invalid employment status.');
        }

        if ($hiredAt !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hiredAt)) {
            throw new RuntimeException('Invalid hire date.');
        }

        if ($retentionUntil !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $retentionUntil)) {
            throw new RuntimeException('Invalid retention date.');
        }

        $allowedProcessingBases = [
            'BDSG Paragraf 26 / DSGVO Art. 6 Abs. 1 lit. b',
            'DSGVO Art. 6 Abs. 1 lit. c',
            'DSGVO Art. 6 Abs. 1 lit. f',
        ];

        if (!in_array($dataProcessingBasis, $allowedProcessingBases, true)) {
            throw new RuntimeException('Invalid data processing basis.');
        }
    }

    private function quickActionsForDepartment(array $department): array
    {
        $slug = (string) ($department['slug'] ?? '');
        $actions = [
            [
                'label' => 'Arbeitsbereich oeffnen',
                'href' => '/departments/' . $slug,
                'variant' => 'primary',
            ],
            [
                'label' => 'Dokumente',
                'href' => '/departments/' . $slug . '#department-documents',
                'variant' => 'secondary',
            ],
            [
                'label' => 'Dateien',
                'href' => '/departments/' . $slug . '#department-filesystem',
                'variant' => 'secondary',
            ],
        ];

        if (!$this->mayManageDepartment($department)) {
            return $actions;
        }

        $actions[] = [
            'label' => 'Neues Dokument',
            'href' => '/departments/' . $slug . '#department-document-create',
            'variant' => 'secondary',
        ];
        $actions[] = [
            'label' => 'Datei hochladen',
            'href' => '/departments/' . $slug . '#department-file-upload',
            'variant' => 'secondary',
        ];

        if ($this->isInformationTechnologyDepartment($department)) {
            $actions[] = [
                'label' => 'Neue Person',
                'href' => '/departments/' . $slug . '#department-managed-person-create',
                'variant' => 'secondary',
            ];
        }

        if ($this->isHumanResourcesDepartment($department)) {
            $actions[] = [
                'label' => 'Neuer Mitarbeiter',
                'href' => '/departments/' . $slug . '#department-employee-create',
                'variant' => 'secondary',
            ];
            $actions[] = [
                'label' => 'Neue Personalakte',
                'href' => '/departments/' . $slug . '#department-employee-document-upload',
                'variant' => 'secondary',
            ];
        }

        return $actions;
    }

    private function summaryStatsForDepartment(array $department): array
    {
        $departmentId = (int) ($department['id'] ?? 0);
        $departmentSlug = (string) ($department['slug'] ?? '');
        $availableStats = [
            [
                'label' => 'Dokumente',
                'value' => DepartmentDocument::countForDepartment($departmentId),
            ],
            [
                'label' => 'Dateien',
                'value' => (new FilesystemService($this->app))->countDepartmentFiles($departmentSlug),
            ],
        ];

        if ($this->isInformationTechnologyDepartment($department)) {
            $availableStats[] = [
                'label' => 'Verwaltete Konten',
                'value' => User::countProvisionedForDepartment($departmentId),
            ];
        }

        if ($this->isHumanResourcesDepartment($department)) {
            $availableStats[] = [
                'label' => 'Mitarbeiter',
                'value' => Employee::countForDepartment($departmentId),
            ];
            $availableStats[] = [
                'label' => 'Personalakten',
                'value' => EmployeeDocument::countForDepartment($departmentId),
            ];
        }

        $configuredStatLabels = $department['profile']['kpis'] ?? [];

        if (!is_array($configuredStatLabels) || $configuredStatLabels === []) {
            return $availableStats;
        }

        $statsByLabel = [];

        foreach ($availableStats as $stat) {
            $statsByLabel[$stat['label']] = $stat;
        }

        $filteredStats = [];

        foreach ($configuredStatLabels as $label) {
            if (isset($statsByLabel[$label])) {
                $filteredStats[] = $statsByLabel[$label];
            }
        }

        return $filteredStats === [] ? $availableStats : $filteredStats;
    }

    public function withSummaryStats(array $department): array
    {
        if (!isset($department['profile']) || !is_array($department['profile'])) {
            $department = $this->enrichDepartment($department);
        }

        $department['summary_stats'] = $this->summaryStatsForDepartment($department);

        return $department;
    }

    private function enrichDepartment(array $department): array
    {
        $profile = $this->departmentProfile((string) ($department['slug'] ?? ''));

        $department['profile'] = $profile;
        $department['tagline'] = $profile['tagline'];
        $department['focus'] = $profile['focus'];
        $department['hero_text'] = $profile['hero'];
        $department['responsibilities'] = $profile['responsibilities'];
        $department['workflows'] = $profile['workflows'];
        $department['leader_title'] = $profile['leader_title'];
        $department['leader_intro'] = $profile['leader_intro'];
        $department['leader_tasks'] = $profile['leader_tasks'];
        $department['playbook'] = $profile['playbook'];
        $department['detail_partial'] = $profile['detail_partial'];

        return $department;
    }

    private function departmentProfile(string $slug): array
    {
        $defaults = $this->app->config('departments.defaults', []);
        $profiles = $this->app->config('departments.profiles', []);
        $profile = is_array($profiles[$slug] ?? null) ? $profiles[$slug] : [];

        return [
            'tagline' => (string) ($profile['tagline'] ?? $defaults['tagline'] ?? ''),
            'focus' => (string) ($profile['focus'] ?? $defaults['focus'] ?? ''),
            'hero' => (string) ($profile['hero'] ?? $defaults['hero'] ?? ''),
            'leader_title' => (string) ($profile['leader_title'] ?? $defaults['leader_title'] ?? ''),
            'leader_intro' => (string) ($profile['leader_intro'] ?? $defaults['leader_intro'] ?? ''),
            'responsibilities' => $this->normalizeProfileList($profile['responsibilities'] ?? $defaults['responsibilities'] ?? []),
            'workflows' => $this->normalizeProfileList($profile['workflows'] ?? $defaults['workflows'] ?? []),
            'kpis' => $this->normalizeProfileList($profile['kpis'] ?? $defaults['kpis'] ?? []),
            'leader_tasks' => $this->normalizeLeaderTasks($profile['leader_tasks'] ?? $defaults['leader_tasks'] ?? []),
            'playbook' => $this->normalizePlaybook($profile['playbook'] ?? []),
            'detail_partial' => trim((string) ($profile['detail_partial'] ?? '')),
        ];
    }

    private function normalizeProfileList(mixed $items): array
    {
        if (!is_array($items)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $item): string => trim((string) $item),
            $items
        ), static fn (string $item): bool => $item !== ''));
    }

    private function normalizeLeaderTasks(mixed $items): array
    {
        if (!is_array($items)) {
            return [];
        }

        $tasks = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $description = trim((string) ($item['description'] ?? ''));
            $actionLabel = trim((string) ($item['action_label'] ?? ''));
            $actionTarget = trim((string) ($item['action_target'] ?? ''));

            if ($title === '' || $description === '') {
                continue;
            }

            $tasks[] = [
                'title' => $title,
                'description' => $description,
                'action_label' => $actionLabel,
                'action_target' => $actionTarget,
            ];
        }

        return $tasks;
    }

    private function normalizePlaybook(mixed $item): array
    {
        if (!is_array($item)) {
            return [];
        }

        $eyebrow = trim((string) ($item['eyebrow'] ?? ''));
        $title = trim((string) ($item['title'] ?? ''));
        $intro = trim((string) ($item['intro'] ?? ''));
        $items = $this->normalizeProfileList($item['items'] ?? []);

        if ($eyebrow === '' && $title === '' && $intro === '' && $items === []) {
            return [];
        }

        return [
            'eyebrow' => $eyebrow,
            'title' => $title,
            'intro' => $intro,
            'items' => $items,
        ];
    }

    private function isAdmin(array $user): bool
    {
        return ($user['role_name'] ?? null) === 'admin';
    }
}
