<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Policies;

use App\Models\User;
use App\Modules\Industry\Travel\Models\Traveller;

class TravellerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('traveller.view');
    }

    public function view(User $user, Traveller $traveller): bool
    {
        return $user->can('traveller.view') && $this->sameTenant($user, $traveller);
    }

    public function create(User $user): bool
    {
        return $user->can('traveller.create');
    }

    public function update(User $user, Traveller $traveller): bool
    {
        return $user->can('traveller.update') && $this->sameTenant($user, $traveller);
    }

    public function delete(User $user, Traveller $traveller): bool
    {
        return $user->can('traveller.delete') && $this->sameTenant($user, $traveller);
    }

    private function sameTenant(User $user, Traveller $traveller): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $traveller->tenant_id;
    }
}
