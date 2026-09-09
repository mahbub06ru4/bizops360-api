<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Organization\Models\Department;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Deletes a department belonging to the current tenant. Designations attached to
 * the department are detached (their `department_id` is nulled) by the database.
 */
class DeleteDepartment
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Department $department): void
    {
        $this->assertTenantOwns($department);

        $department->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
