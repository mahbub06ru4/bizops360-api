<?php

declare(strict_types=1);

namespace App\Modules\Organization\Policies;

use App\Models\User;
use App\Modules\Organization\Models\Department;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('department.view');
    }

    public function view(User $user, Department $department): bool
    {
        return $user->can('department.view') && $this->sameTenant($user, $department);
    }

    public function create(User $user): bool
    {
        return $user->can('department.create');
    }

    public function update(User $user, Department $department): bool
    {
        return $user->can('department.update') && $this->sameTenant($user, $department);
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->can('department.delete') && $this->sameTenant($user, $department);
    }

    private function sameTenant(User $user, Department $department): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $department->tenant_id;
    }
}
