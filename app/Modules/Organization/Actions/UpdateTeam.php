<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Organization\Data\TeamData;
use App\Modules\Organization\Models\Employee;
use App\Modules\Organization\Models\Team;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Updates a team belonging to the current tenant.
 */
class UpdateTeam
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Team $team, TeamData $data): Team
    {
        $this->assertTenantOwns($team);
        $this->assertReferenceOwned($data->leadEmployeeId, Employee::class);

        $team->fill($data->toAttributes())->save();

        return $team->refresh();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
