<?php

declare(strict_types=1);

namespace App\Modules\CRM\Policies;

use App\Models\User;
use App\Modules\CRM\Models\Customer;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('customer.view');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->can('customer.view')
            && $this->sameTenant($user, $customer)
            && ($user->can('customer.view_all') || $this->isInvolved($user, $customer));
    }

    public function create(User $user): bool
    {
        return $user->can('customer.create');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->can('customer.update')
            && $this->sameTenant($user, $customer)
            && ($user->can('customer.view_all') || $this->isInvolved($user, $customer));
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->can('customer.delete') && $this->sameTenant($user, $customer);
    }

    private function isInvolved(User $user, Customer $customer): bool
    {
        if ($customer->created_by === $user->getKey()) {
            return true;
        }

        return $customer->owner !== null && $customer->owner->user_id === $user->getKey();
    }

    private function sameTenant(User $user, Customer $customer): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $customer->tenant_id;
    }
}
