<?php

declare(strict_types=1);

namespace App\Modules\Organization\Policies;

use App\Models\User;
use App\Modules\Organization\Models\Employee;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('employee.view');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->can('employee.view') && $this->sameTenant($user, $employee);
    }

    public function create(User $user): bool
    {
        return $user->can('employee.create');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->can('employee.update') && $this->sameTenant($user, $employee);
    }

    public function terminate(User $user, Employee $employee): bool
    {
        return $user->can('employee.terminate') && $this->sameTenant($user, $employee);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->can('employee.delete') && $this->sameTenant($user, $employee);
    }

    private function sameTenant(User $user, Employee $employee): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $employee->tenant_id;
    }
}
