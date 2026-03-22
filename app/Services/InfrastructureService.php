<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\InfrastructureService as InfrastructureServiceModel;

final class InfrastructureService
{
    public function all(): array
    {
        return InfrastructureServiceModel::all();
    }
}
