<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Policies;

use App\Models\User;
use App\Modules\Industry\RealEstate\Models\PropertyRequirement;

class PropertyRequirementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('property_requirement.view');
    }

    public function view(User $user, PropertyRequirement $requirement): bool
    {
        return $user->can('property_requirement.view') && $this->sameTenant($user, $requirement);
    }

    public function create(User $user): bool
    {
        return $user->can('property_requirement.create');
    }

    public function update(User $user, PropertyRequirement $requirement): bool
    {
        return $user->can('property_requirement.update') && $this->sameTenant($user, $requirement);
    }

    private function sameTenant(User $user, PropertyRequirement $requirement): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $requirement->tenant_id;
    }
}
