<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Policies;

use App\Models\User;
use App\Modules\Industry\RealEstate\Models\Building;

class BuildingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('building.view');
    }

    public function view(User $user, Building $building): bool
    {
        return $user->can('building.view') && $this->sameTenant($user, $building);
    }

    public function create(User $user): bool
    {
        return $user->can('building.create');
    }

    public function update(User $user, Building $building): bool
    {
        return $user->can('building.update') && $this->sameTenant($user, $building);
    }

    public function delete(User $user, Building $building): bool
    {
        return $user->can('building.delete') && $this->sameTenant($user, $building);
    }

    private function sameTenant(User $user, Building $building): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $building->tenant_id;
    }
}
