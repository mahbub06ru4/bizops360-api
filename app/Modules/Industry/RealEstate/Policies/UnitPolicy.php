<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Policies;

use App\Models\User;
use App\Modules\Industry\RealEstate\Models\Unit;

class UnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('unit.view');
    }

    public function view(User $user, Unit $unit): bool
    {
        return $user->can('unit.view') && $this->sameTenant($user, $unit);
    }

    public function create(User $user): bool
    {
        return $user->can('unit.create');
    }

    public function update(User $user, Unit $unit): bool
    {
        return $user->can('unit.update') && $this->sameTenant($user, $unit);
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $user->can('unit.delete') && $this->sameTenant($user, $unit);
    }

    private function sameTenant(User $user, Unit $unit): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $unit->tenant_id;
    }
}
