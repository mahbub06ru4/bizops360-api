<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Organization\Data\DepartmentData;
use App\Modules\Organization\Models\Branch;
use App\Modules\Organization\Models\Department;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Creates a department for the current tenant, optionally under a branch.
 */
class CreateDepartment
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(DepartmentData $data): Department
    {
        if ($data->branchId !== null) {
            $this->assertTenantOwns(Branch::query()->findOrFail($data->branchId));
        }

        $department = new Department($data->toAttributes());
        $department->tenant_id = $this->currentTenantId();
        $department->save();

        return $department;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
