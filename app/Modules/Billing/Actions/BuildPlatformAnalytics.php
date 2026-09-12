<?php

declare(strict_types=1);

namespace App\Modules\Billing\Actions;

use App\Models\User;
use App\Modules\Billing\Domain\SubscriptionStatus;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\Subscription;
use App\Modules\Finance\Domain\Money;
use App\Modules\Tenant\Models\Tenant;

/**
 * Platform-wide SaaS metrics — spans every tenant, so it only runs for a
 * platform admin request (no tenant bound, see EnsurePlatformAdmin). Every
 * query here is intentionally unscoped.
 */
class BuildPlatformAnalytics
{
    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        $tenantsByIndustry = Tenant::query()
            ->selectRaw('coalesce(industry, ?) as industry, count(*) as total', ['unspecified'])
            ->groupBy('industry')
            ->pluck('total', 'industry');

        $subscriptionsByStatus = Subscription::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $subscriptionsByPlan = Subscription::query()
            ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
            ->selectRaw('plans.code as plan_code, count(*) as total')
            ->groupBy('plans.code')
            ->pluck('total', 'plan_code');

        $mrr = Money::zero();

        Subscription::query()
            ->with('plan')
            ->whereIn('status', [SubscriptionStatus::Trialing, SubscriptionStatus::Active])
            ->get()
            ->each(function (Subscription $subscription) use (&$mrr): void {
                $monthly = $subscription->plan->billing_interval === 'year'
                    ? Money::fromDecimal($subscription->plan->price_amount)->minorUnits / 12
                    : Money::fromDecimal($subscription->plan->price_amount)->minorUnits;

                $mrr = $mrr->add(Money::fromMinorUnits((int) round($monthly)));
            });

        return [
            'tenants' => [
                'total' => Tenant::query()->count(),
                'by_industry' => $tenantsByIndustry,
            ],
            'users' => [
                'total' => User::query()->whereNotNull('tenant_id')->count(),
            ],
            'subscriptions' => [
                'total' => Subscription::query()->count(),
                'by_status' => $subscriptionsByStatus,
                'by_plan' => $subscriptionsByPlan,
            ],
            'plans' => [
                'active' => Plan::query()->where('is_active', true)->count(),
            ],
            'estimated_mrr' => $mrr->toDecimalString(),
        ];
    }
}
