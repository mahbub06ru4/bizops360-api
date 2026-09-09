<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions\Concerns;

use App\Models\User;
use App\Modules\Authorization\Roles;
use Illuminate\Validation\ValidationException;

/**
 * Keeps every tenant with at least one owner. The spatie permissions team id is
 * expected to already point at the current tenant (set by ResolveTenant), so the
 * owner count is naturally tenant-scoped.
 */
trait GuardsTenantOwnership
{
    /**
     * @param  list<string>  $newRoles
     */
    protected function assertNotRemovingLastOwner(User $user, array $newRoles): void
    {
        if (! $user->hasRole(Roles::OWNER) || in_array(Roles::OWNER, $newRoles, true)) {
            return;
        }

        if ($this->ownerCount() <= 1) {
            throw ValidationException::withMessages([
                'roles' => 'The tenant must keep at least one owner.',
            ]);
        }
    }

    protected function assertNotLastOwner(User $user): void
    {
        if ($user->hasRole(Roles::OWNER) && $this->ownerCount() <= 1) {
            throw ValidationException::withMessages([
                'user' => 'The tenant must keep at least one owner.',
            ]);
        }
    }

    private function ownerCount(): int
    {
        return User::query()->role(Roles::OWNER)->count();
    }
}
