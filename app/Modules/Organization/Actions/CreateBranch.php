<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Organization\Data\BranchData;
use App\Modules\Organization\Models\Branch;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Creates a branch for the current tenant.
 */
class CreateBranch
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(BranchData $data): Branch
    {
        $branch = new Branch($data->toAttributes());
        $branch->tenant_id = $this->currentTenantId();
        $branch->save();

        return $branch;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
