<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Organization\Data\TeamData;
use App\Modules\Organization\Models\Employee;
use App\Modules\Organization\Models\Team;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Creates a team for the current tenant.
 */
class CreateTeam
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(TeamData $data): Team
    {
        $this->assertReferenceOwned($data->leadEmployeeId, Employee::class);

        $team = new Team($data->toAttributes());
        $team->tenant_id = $this->currentTenantId();
        $team->save();

        return $team;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
