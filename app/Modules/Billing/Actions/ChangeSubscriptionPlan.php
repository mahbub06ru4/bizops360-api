<?php

declare(strict_types=1);

namespace App\Modules\Billing\Actions;

use App\Modules\Billing\Data\ChangePlanData;
use App\Modules\Billing\Domain\SubscriptionStatus;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\Subscription;
use InvalidArgumentException;

class ChangeSubscriptionPlan
{
    public function handle(Subscription $subscription, ChangePlanData $data): Subscription
    {
        $plan = Plan::query()->where('code', $data->planCode)->where('is_active', true)->first();

        if ($plan === null) {
            throw new InvalidArgumentException("Unknown or inactive plan code [{$data->planCode}].");
        }

        $subscription->plan_id = $plan->getKey();

        if ($subscription->status === SubscriptionStatus::Canceled) {
            $subscription->status = SubscriptionStatus::Active;
            $subscription->canceled_at = null;
            $subscription->current_period_ends_at = now()->addMonth();
        }

        $subscription->save();

        return $subscription->fresh('plan');
    }
}
