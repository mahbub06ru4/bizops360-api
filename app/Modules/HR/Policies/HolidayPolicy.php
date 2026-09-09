<?php

declare(strict_types=1);

namespace App\Modules\HR\Policies;

use App\Models\User;
use App\Modules\HR\Models\Holiday;

class HolidayPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('holiday.view');
    }

    public function view(User $user, Holiday $holiday): bool
    {
        return $user->can('holiday.view') && $this->sameTenant($user, $holiday);
    }

    public function create(User $user): bool
    {
        return $user->can('holiday.create');
    }

    public function update(User $user, Holiday $holiday): bool
    {
        return $user->can('holiday.update') && $this->sameTenant($user, $holiday);
    }

    public function delete(User $user, Holiday $holiday): bool
    {
        return $user->can('holiday.delete') && $this->sameTenant($user, $holiday);
    }

    private function sameTenant(User $user, Holiday $holiday): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $holiday->tenant_id;
    }
}
