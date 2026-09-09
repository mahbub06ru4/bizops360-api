<?php

declare(strict_types=1);

namespace App\Modules\Organization\Policies;

use App\Models\User;
use App\Modules\Organization\Models\Team;

class TeamPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('team.view');
    }

    public function view(User $user, Team $team): bool
    {
        return $user->can('team.view') && $this->sameTenant($user, $team);
    }

    public function create(User $user): bool
    {
        return $user->can('team.create');
    }

    public function update(User $user, Team $team): bool
    {
        return $user->can('team.update') && $this->sameTenant($user, $team);
    }

    public function delete(User $user, Team $team): bool
    {
        return $user->can('team.delete') && $this->sameTenant($user, $team);
    }

    private function sameTenant(User $user, Team $team): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $team->tenant_id;
    }
}
