<?php

declare(strict_types=1);

namespace App\Modules\HR\Policies;

use App\Models\User;

class AttendanceSettingPolicy
{
    public function view(User $user): bool
    {
        return $user->can('attendance.view');
    }

    public function manage(User $user): bool
    {
        return $user->can('attendance.manage_settings');
    }

    public function manageLocation(User $user): bool
    {
        return $user->can('attendance.manage');
    }
}
