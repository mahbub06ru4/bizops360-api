<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Policies;

use App\Models\User;
use App\Modules\Industry\RealEstate\Models\InstallmentPlan;

class InstallmentPlanPolicy
{
    public function view(User $user, InstallmentPlan $plan): bool
    {
        return $user->can('installment.view') && $this->sameTenant($user, $plan);
    }

    public function create(User $user): bool
    {
        return $user->can('installment.manage');
    }

    private function sameTenant(User $user, InstallmentPlan $plan): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $plan->tenant_id;
    }
}
