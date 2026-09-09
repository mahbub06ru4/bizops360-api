<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Organization\Data\TeamMembersData;
use App\Modules\Organization\Models\Employee;
use App\Modules\Organization\Models\Team;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Replaces a team's member list. Every employee id is re-checked against the
 * current tenant before the pivot is synced.
 */
class SetTeamMembers
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Team $team, TeamMembersData $data): Team
    {
        $this->assertTenantOwns($team);

        foreach ($data->employeeIds as $employeeId) {
            $this->assertReferenceOwned($employeeId, Employee::class);
        }

        $team->members()->sync($data->employeeIds);

        return $team->load('members');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
