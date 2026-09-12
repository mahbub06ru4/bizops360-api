<?php

declare(strict_types=1);

namespace App\Modules\Billing\Actions;

use App\Modules\Billing\Domain\SubscriptionStatus;
use App\Modules\Billing\Models\Subscription;

class CancelSubscription
{
    public function handle(Subscription $subscription): Subscription
    {
        $subscription->status = SubscriptionStatus::Canceled;
        $subscription->canceled_at = now();
        $subscription->save();

        return $subscription->fresh('plan');
    }
}
