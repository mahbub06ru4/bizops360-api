<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Organization\Models\Designation;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Deletes a designation belonging to the current tenant.
 */
class DeleteDesignation
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Designation $designation): void
    {
        $this->assertTenantOwns($designation);

        $designation->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
