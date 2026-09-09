<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Organization\Data\DesignationData;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Designation;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Updates a designation belonging to the current tenant.
 */
class UpdateDesignation
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Designation $designation, DesignationData $data): Designation
    {
        $this->assertTenantOwns($designation);

        if ($data->departmentId !== null) {
            $this->assertTenantOwns(Department::query()->findOrFail($data->departmentId));
        }

        $designation->fill($data->toAttributes())->save();

        return $designation->refresh();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
