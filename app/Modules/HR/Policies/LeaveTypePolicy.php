<?php

declare(strict_types=1);

namespace App\Modules\HR\Policies;

use App\Models\User;
use App\Modules\HR\Models\LeaveType;

class LeaveTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('leave_type.view');
    }

    public function view(User $user, LeaveType $leaveType): bool
    {
        return $user->can('leave_type.view') && $this->sameTenant($user, $leaveType);
    }

    public function create(User $user): bool
    {
        return $user->can('leave_type.create');
    }

    public function update(User $user, LeaveType $leaveType): bool
    {
        return $user->can('leave_type.update') && $this->sameTenant($user, $leaveType);
    }

    public function delete(User $user, LeaveType $leaveType): bool
    {
        return $user->can('leave_type.delete') && $this->sameTenant($user, $leaveType);
    }

    private function sameTenant(User $user, LeaveType $leaveType): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $leaveType->tenant_id;
    }
}
