<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\FilesystemService;
use App\Models\InfrastructureService as InfrastructureServiceModel;

final class InfrastructureService
{
    public function all(): array
    {
        return InfrastructureServiceModel::all();
    }

    public function departmentFileBrowser(DepartmentService $departmentService): array
    {
        $shares = [];
        $filesystem = new FilesystemService($departmentService->app());

        foreach ($departmentService->listVisibleDepartments() as $department) {
            $shares[] = [
                'department' => $department,
                'files' => $filesystem->listDepartmentFiles((string) $department['slug']),
            ];
        }

        return $shares;
    }
}
