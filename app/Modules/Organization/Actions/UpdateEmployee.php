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
 * Updates an employee belonging to the current tenant.
 */
class UpdateEmployee
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Employee $employee, EmployeeData $data): Employee
    {
        $this->assertTenantOwns($employee);

        $this->assertReferenceOwned($data->userId, User::class);
        $this->assertReferenceOwned($data->branchId, Branch::class);
        $this->assertReferenceOwned($data->departmentId, Department::class);
        $this->assertReferenceOwned($data->designationId, Designation::class);

        $employee->fill($data->toAttributes())->save();

        return $employee->refresh();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
