<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Policies;

use App\Models\User;
use App\Modules\Authorization\Roles;
use App\Modules\Industry\RealEstate\Models\LandRecord;

/**
 * Legal/private land data (mouza, JL/khatian/dag no.). Only `owner`/`admin`
 * hold `land_record.view`/`land_record.manage` (see {@see Roles})
 * — never exposed to a manager, staff, or buyer audience.
 */
class LandRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('land_record.view');
    }

    public function view(User $user, LandRecord $record): bool
    {
        return $user->can('land_record.view') && $this->sameTenant($user, $record);
    }

    public function create(User $user): bool
    {
        return $user->can('land_record.manage');
    }

    private function sameTenant(User $user, LandRecord $record): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $record->tenant_id;
    }
}
