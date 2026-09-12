<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Billing\Domain\SubscriptionStatus;
use App\Modules\Billing\Models\Plan;

it('rejects platform analytics for a normal tenant user, even an owner', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->getJson('/api/v1/platform/analytics')
        ->assertForbidden();
});

it('gives a platform admin unscoped totals across every tenant', function (): void {
    $tenantA = makeTenant(['slug' => 'plat-a', 'industry' => 'travel']);
    $tenantB = makeTenant(['slug' => 'plat-b', 'industry' => 'real_estate']);

    $plan = Plan::factory()->create(['code' => 'plat-plan', 'price_amount' => '30.00', 'billing_interval' => 'month']);
    createSubscription($tenantA, $plan, SubscriptionStatus::Active);
    createSubscription($tenantB, $plan, SubscriptionStatus::Trialing);
    clearTenantContext();

    $admin = User::factory()->create(['tenant_id' => null, 'is_platform_admin' => true]);

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/platform/analytics')
        ->assertOk()
        ->assertJsonPath('data.tenants.total', 2)
        ->assertJsonPath('data.subscriptions.total', 2)
        ->assertJsonPath('data.estimated_mrr', '60.00');
});
