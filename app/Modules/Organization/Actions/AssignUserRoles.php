<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Models\User;
use App\Modules\Organization\Actions\Concerns\GuardsTenantOwnership;
use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Organization\Data\UserRolesData;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Replaces the role set assigned to a user within the current tenant.
 */
class AssignUserRoles
{
    use GuardsTenantOwnership;
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(User $user, UserRolesData $data): User
    {
        $this->assertTenantOwns($user);
        $this->assertNotRemovingLastOwner($user, $data->roles);

        $user->syncRoles($data->roles);

        return $user->fresh(['roles']);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
