<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Models\User;
use App\Modules\Organization\Actions\Concerns\GuardsTenantOwnership;
use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

/**
 * Removes a user from the current tenant. Cannot remove yourself or the tenant's
 * last remaining owner.
 */
class DeleteUser
{
    use GuardsTenantOwnership;
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(User $user, User $actor): void
    {
        $this->assertTenantOwns($user);

        if ($user->is($actor)) {
            throw ValidationException::withMessages([
                'user' => 'You cannot delete your own account.',
            ]);
        }

        $this->assertNotLastOwner($user);

        $user->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
