<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Permanently deletes an employee record of the current tenant. The linked login
 * user (if any) is left untouched.
 */
class DeleteEmployee
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Employee $employee): void
    {
        $this->assertTenantOwns($employee);

        $employee->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
