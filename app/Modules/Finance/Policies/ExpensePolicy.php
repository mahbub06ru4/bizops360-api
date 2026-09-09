<?php

declare(strict_types=1);

namespace App\Modules\Finance\Policies;

use App\Models\User;
use App\Modules\Finance\Models\Expense;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('expense.view');
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->can('expense.view') && $this->sameTenant($user, $expense);
    }

    public function create(User $user): bool
    {
        return $user->can('expense.create');
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->can('expense.update') && $this->sameTenant($user, $expense);
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->can('expense.delete') && $this->sameTenant($user, $expense);
    }

    private function sameTenant(User $user, Expense $expense): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $expense->tenant_id;
    }
}
