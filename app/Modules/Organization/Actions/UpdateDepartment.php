<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Organization\Data\DepartmentData;
use App\Modules\Organization\Models\Branch;
use App\Modules\Organization\Models\Department;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Updates a department belonging to the current tenant.
 */
class UpdateDepartment
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Department $department, DepartmentData $data): Department
    {
        $this->assertTenantOwns($department);

        if ($data->branchId !== null) {
            $this->assertTenantOwns(Branch::query()->findOrFail($data->branchId));
        }

        $department->fill($data->toAttributes())->save();

        return $department->refresh();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
