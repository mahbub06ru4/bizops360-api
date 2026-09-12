<?php

declare(strict_types=1);

namespace App\Modules\Billing\Actions;

use App\Modules\Billing\Domain\SubscriptionStatus;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\Subscription;
use App\Modules\Identity\Actions\RegisterTenant;
use App\Modules\Tenant\Models\Tenant;
use Spatie\Permission\Models\Permission;

/**
 * Called once, when a tenant is registered (see
 * {@see RegisterTenant}) — every new tenant
 * starts on a free trial of the default plan.
 *
 * Self-healing like {@see Permission::findOrCreate()}:
 * if the requested plan code doesn't exist yet (a fresh test database, an
 * environment where PlansSeeder hasn't run) it creates a bare free plan
 * rather than failing registration — the real catalogue always wins once
 * PlansSeeder has actually run, `updateOrCreate`-ing over this bootstrap row.
 */
class StartTrialSubscription
{
    public function handle(Tenant $tenant, string $planCode = 'starter', int $trialDays = 14): Subscription
    {
        $plan = Plan::query()->firstOrCreate(
            ['code' => $planCode],
            ['name' => ucfirst($planCode), 'price_amount' => '0.00', 'billing_interval' => 'month', 'is_active' => true],
        );

        // `tenant_id` isn't fillable (it's never client input) — set it
        // explicitly rather than relying on the caller having already bound
        // TenantContext for BelongsToTenant's auto-fill to reach.
        $subscription = new Subscription([
            'plan_id' => $plan->getKey(),
            'status' => SubscriptionStatus::Trialing,
            'trial_ends_at' => now()->addDays($trialDays),
        ]);
        $subscription->tenant_id = $tenant->getKey();
        $subscription->save();

        return $subscription;
    }
}
