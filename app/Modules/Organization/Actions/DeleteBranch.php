<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Organization\Models\Branch;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Deletes a branch belonging to the current tenant. Departments attached to the
 * branch are detached (their `branch_id` is nulled) by the database.
 */
class DeleteBranch
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Branch $branch): void
    {
        $this->assertTenantOwns($branch);

        $branch->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
