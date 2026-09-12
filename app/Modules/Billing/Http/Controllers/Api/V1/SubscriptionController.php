<?php

declare(strict_types=1);

namespace App\Modules\Billing\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Billing\Actions\CancelSubscription;
use App\Modules\Billing\Actions\ChangeSubscriptionPlan;
use App\Modules\Billing\Http\Requests\ChangePlanRequest;
use App\Modules\Billing\Http\Resources\SubscriptionResource;
use App\Modules\Billing\Models\Subscription;

class SubscriptionController extends Controller
{
    public function show(): SubscriptionResource
    {
        $subscription = Subscription::query()->with('plan')->firstOrFail();
        $this->authorize('view', $subscription);

        return SubscriptionResource::make($subscription);
    }

    public function update(ChangePlanRequest $request, ChangeSubscriptionPlan $action): SubscriptionResource
    {
        $subscription = Subscription::query()->firstOrFail();
        $this->authorize('update', $subscription);

        return SubscriptionResource::make($action->handle($subscription, $request->toData()));
    }

    public function cancel(CancelSubscription $action): SubscriptionResource
    {
        $subscription = Subscription::query()->firstOrFail();
        $this->authorize('update', $subscription);

        return SubscriptionResource::make($action->handle($subscription));
    }
}
