<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Policies;

use App\Models\User;
use App\Modules\Industry\RealEstate\Models\Installment;

class InstallmentPolicy
{
    public function view(User $user, Installment $installment): bool
    {
        return $user->can('installment.view') && $this->sameTenant($user, $installment);
    }

    public function update(User $user, Installment $installment): bool
    {
        return $user->can('installment.manage') && $this->sameTenant($user, $installment);
    }

    private function sameTenant(User $user, Installment $installment): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $installment->tenant_id;
    }
}
