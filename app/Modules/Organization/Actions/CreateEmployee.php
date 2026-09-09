<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Models\User;
use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Organization\Data\EmployeeData;
use App\Modules\Organization\Models\Branch;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Designation;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Creates an employee for the current tenant, validating that every referenced
 * record (login user, branch, department, designation) belongs to that tenant.
 */
class CreateEmployee
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(EmployeeData $data): Employee
    {
        $this->assertReferenceOwned($data->userId, User::class);
        $this->assertReferenceOwned($data->branchId, Branch::class);
        $this->assertReferenceOwned($data->departmentId, Department::class);
        $this->assertReferenceOwned($data->designationId, Designation::class);

        $employee = new Employee($data->toAttributes());
        $employee->tenant_id = $this->currentTenantId();
        $employee->save();

        return $employee;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
