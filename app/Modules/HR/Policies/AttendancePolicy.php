<?php

declare(strict_types=1);

namespace App\Modules\HR\Policies;

use App\Models\User;
use App\Modules\HR\Models\Attendance;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('attendance.view');
    }

    public function view(User $user, Attendance $attendance): bool
    {
        return $user->can('attendance.view')
            && $this->sameTenant($user, $attendance)
            && ($user->can('attendance.view_all') || $this->belongsToUser($user, $attendance));
    }

    public function checkIn(User $user): bool
    {
        return $user->can('attendance.check_in');
    }

    public function record(User $user): bool
    {
        return $user->can('attendance.record');
    }

    private function belongsToUser(User $user, Attendance $attendance): bool
    {
        return $attendance->employee !== null && $attendance->employee->user_id === $user->getKey();
    }

    private function sameTenant(User $user, Attendance $attendance): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $attendance->tenant_id;
    }
}
