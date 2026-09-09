<?php

declare(strict_types=1);

namespace App\Modules\Finance\Policies;

use App\Models\User;
use App\Modules\Finance\Models\Income;

class IncomePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('income.view');
    }

    public function view(User $user, Income $income): bool
    {
        return $user->can('income.view') && $this->sameTenant($user, $income);
    }

    public function create(User $user): bool
    {
        return $user->can('income.create');
    }

    public function update(User $user, Income $income): bool
    {
        return $user->can('income.update') && $this->sameTenant($user, $income);
    }

    public function delete(User $user, Income $income): bool
    {
        return $user->can('income.delete') && $this->sameTenant($user, $income);
    }

    private function sameTenant(User $user, Income $income): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $income->tenant_id;
    }
}
