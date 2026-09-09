<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Organization\Data\BranchData;
use App\Modules\Organization\Models\Branch;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Updates a branch belonging to the current tenant.
 */
class UpdateBranch
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Branch $branch, BranchData $data): Branch
    {
        $this->assertTenantOwns($branch);

        $branch->fill($data->toAttributes())->save();

        return $branch->refresh();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
