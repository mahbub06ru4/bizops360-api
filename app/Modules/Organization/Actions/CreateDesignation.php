<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Organization\Data\DesignationData;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Designation;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Creates a designation for the current tenant, optionally under a department.
 */
class CreateDesignation
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(DesignationData $data): Designation
    {
        if ($data->departmentId !== null) {
            $this->assertTenantOwns(Department::query()->findOrFail($data->departmentId));
        }

        $designation = new Designation($data->toAttributes());
        $designation->tenant_id = $this->currentTenantId();
        $designation->save();

        return $designation;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
