<?php

declare(strict_types=1);

namespace App\Modules\HR\Policies;

use App\Models\User;
use App\Modules\HR\Models\LeaveRequest;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('leave.view');
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->can('leave.view')
            && $this->sameTenant($user, $leaveRequest)
            && ($user->can('leave.approve') || $this->belongsToUser($user, $leaveRequest));
    }

    public function create(User $user): bool
    {
        return $user->can('leave.request');
    }

    public function decide(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->can('leave.approve') && $this->sameTenant($user, $leaveRequest);
    }

    public function cancel(User $user, LeaveRequest $leaveRequest): bool
    {
        return $this->sameTenant($user, $leaveRequest)
            && ($user->can('leave.approve') || $this->belongsToUser($user, $leaveRequest));
    }

    private function belongsToUser(User $user, LeaveRequest $leaveRequest): bool
    {
        return $leaveRequest->employee !== null && $leaveRequest->employee->user_id === $user->getKey();
    }

    private function sameTenant(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $leaveRequest->tenant_id;
    }
}
