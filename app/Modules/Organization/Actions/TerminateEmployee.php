<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Organization\Domain\EmploymentStatus;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Marks an employee of the current tenant as terminated. Kept as its own use case
 * (rather than a generic status update) because later phases hang offboarding
 * side effects — final payroll, document retention, access revocation — here.
 */
class TerminateEmployee
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Employee $employee): Employee
    {
        $this->assertTenantOwns($employee);

        $employee->employment_status = EmploymentStatus::Terminated;
        $employee->save();

        return $employee->refresh();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
