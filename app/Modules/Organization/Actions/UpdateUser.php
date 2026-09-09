<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Models\User;
use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Organization\Data\UpdateUserData;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Updates the profile of a user belonging to the current tenant.
 */
class UpdateUser
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(User $user, UpdateUserData $data): User
    {
        $this->assertTenantOwns($user);

        $user->name = $data->name;
        $user->email = $data->email;
        $user->save();

        return $user->fresh(['roles']);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
