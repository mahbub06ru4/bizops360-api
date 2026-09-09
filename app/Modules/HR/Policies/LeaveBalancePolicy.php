<?php

declare(strict_types=1);

namespace App\Modules\HR\Policies;

use App\Models\User;
use App\Modules\HR\Models\LeaveBalance;

class LeaveBalancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('leave.view');
    }

    public function view(User $user, LeaveBalance $leaveBalance): bool
    {
        return $user->can('leave.view') && $this->sameTenant($user, $leaveBalance);
    }

    public function manage(User $user): bool
    {
        return $user->can('leave.manage_balance');
    }

    private function sameTenant(User $user, LeaveBalance $leaveBalance): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $leaveBalance->tenant_id;
    }
}
