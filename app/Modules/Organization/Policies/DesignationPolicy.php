<?php

declare(strict_types=1);

namespace App\Modules\Organization\Policies;

use App\Models\User;
use App\Modules\Organization\Models\Designation;

class DesignationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('designation.view');
    }

    public function view(User $user, Designation $designation): bool
    {
        return $user->can('designation.view') && $this->sameTenant($user, $designation);
    }

    public function create(User $user): bool
    {
        return $user->can('designation.create');
    }

    public function update(User $user, Designation $designation): bool
    {
        return $user->can('designation.update') && $this->sameTenant($user, $designation);
    }

    public function delete(User $user, Designation $designation): bool
    {
        return $user->can('designation.delete') && $this->sameTenant($user, $designation);
    }

    private function sameTenant(User $user, Designation $designation): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $designation->tenant_id;
    }
}
