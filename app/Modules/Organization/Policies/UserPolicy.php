<?php

declare(strict_types=1);

namespace App\Modules\Organization\Policies;

use App\Models\User;

/**
 * Guards tenant user administration. `$user` is the acting administrator;
 * `$model` is the user being administered.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('user.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('user.view') && $this->sameTenant($user, $model);
    }

    public function create(User $user): bool
    {
        return $user->can('user.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('user.update') && $this->sameTenant($user, $model);
    }

    public function assignRoles(User $user, User $model): bool
    {
        return $user->can('user.assign_roles') && $this->sameTenant($user, $model);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can('user.delete') && $this->sameTenant($user, $model);
    }

    private function sameTenant(User $user, User $model): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $model->tenant_id;
    }
}
