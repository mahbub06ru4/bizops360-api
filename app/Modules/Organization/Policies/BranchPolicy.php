<?php

declare(strict_types=1);

namespace App\Modules\Organization\Policies;

use App\Models\User;
use App\Modules\Organization\Models\Branch;

class BranchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('branch.view');
    }

    public function view(User $user, Branch $branch): bool
    {
        return $user->can('branch.view') && $this->sameTenant($user, $branch);
    }

    public function create(User $user): bool
    {
        return $user->can('branch.create');
    }

    public function update(User $user, Branch $branch): bool
    {
        return $user->can('branch.update') && $this->sameTenant($user, $branch);
    }

    public function delete(User $user, Branch $branch): bool
    {
        return $user->can('branch.delete') && $this->sameTenant($user, $branch);
    }

    private function sameTenant(User $user, Branch $branch): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $branch->tenant_id;
    }
}
