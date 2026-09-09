<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Organization\Models\Team;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Deletes a team of the current tenant. Membership rows are removed by the
 * database; the employees themselves are untouched.
 */
class DeleteTeam
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Team $team): void
    {
        $this->assertTenantOwns($team);

        $team->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
