<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Policies;

use App\Models\User;
use App\Modules\Tenant\Models\Tenant;

/**
 * Guards the current tenant's own company profile. A user may only ever see or
 * change the tenant they belong to.
 */
class CompanyPolicy
{
    public function view(User $user, Tenant $tenant): bool
    {
        return $user->can('tenant.settings.view') && $user->tenant_id === $tenant->getKey();
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $user->can('tenant.settings.update') && $user->tenant_id === $tenant->getKey();
    }
}
