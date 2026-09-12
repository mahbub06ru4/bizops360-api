<?php

declare(strict_types=1);

namespace App\Modules\Billing\Policies;

use App\Models\User;
use App\Modules\Billing\Models\Subscription;

class SubscriptionPolicy
{
    public function view(User $user, Subscription $subscription): bool
    {
        return $user->tenant_id === $subscription->tenant_id && $user->can('billing.view');
    }

    public function update(User $user, Subscription $subscription): bool
    {
        return $user->tenant_id === $subscription->tenant_id && $user->can('billing.manage');
    }
}
