<?php

declare(strict_types=1);

use App\Modules\Billing\Domain\SubscriptionStatus;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\Subscription;

it('starts every newly registered tenant on a trial subscription', function (): void {
    Plan::factory()->create(['code' => 'starter', 'price_amount' => '0.00']);

    $response = $this->postJson('/api/v1/auth/register', [
        'company_name' => 'Fresh Co',
        'owner_name' => 'Owner',
        'owner_email' => 'owner@fresh.test',
        'owner_password' => 'password1234',
        'owner_password_confirmation' => 'password1234',
    ])->assertCreated();

    $tenantId = $response->json('data.tenant.id');
    $subscription = Subscription::where('tenant_id', $tenantId)->first();

    expect($subscription)->not->toBeNull()
        ->and($subscription->status)->toBe(SubscriptionStatus::Trialing)
        ->and($subscription->plan->code)->toBe('starter');
});

it('lists the active plan catalogue', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    Plan::factory()->create(['code' => 'growth', 'is_active' => true]);
    Plan::factory()->create(['code' => 'retired', 'is_active' => false]);
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')
        ->getJson('/api/v1/billing/plans')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.code', 'growth');
});

it('shows, changes and cancels a tenant subscription for billing.manage roles only', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    $staff = makeUser($tenant, 'staff');
    $plan = Plan::factory()->create(['code' => 'growth']);

    $subscription = Subscription::create([
        'tenant_id' => $tenant->getKey(),
        'plan_id' => $plan->getKey(),
        'status' => SubscriptionStatus::Trialing,
        'trial_ends_at' => now()->addDays(3),
    ]);
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->getJson('/api/v1/billing/subscription')
        ->assertOk()
        ->assertJsonPath('data.status', 'trialing');

    $this->actingAs($staff, 'sanctum')
        ->putJson('/api/v1/billing/subscription', ['plan_code' => $plan->code])
        ->assertForbidden();

    $this->actingAs($owner, 'sanctum')
        ->postJson('/api/v1/billing/subscription/cancel')
        ->assertOk()
        ->assertJsonPath('data.status', 'canceled');

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::Canceled);
});

it('never leaks another tenant\'s subscription', function (): void {
    $tenantA = makeTenant(['slug' => 'bill-a']);
    $ownerA = makeUser($tenantA, 'owner');
    $planA = Plan::factory()->create(['code' => 'plan-a']);
    Subscription::create([
        'tenant_id' => $tenantA->getKey(),
        'plan_id' => $planA->getKey(),
        'status' => SubscriptionStatus::Active,
    ]);

    $tenantB = makeTenant(['slug' => 'bill-b']);
    makeUser($tenantB, 'owner');
    $planB = Plan::factory()->create(['code' => 'plan-b']);
    Subscription::create([
        'tenant_id' => $tenantB->getKey(),
        'plan_id' => $planB->getKey(),
        'status' => SubscriptionStatus::Active,
    ]);
    clearTenantContext();

    $this->actingAs($ownerA, 'sanctum')
        ->getJson('/api/v1/billing/subscription')
        ->assertOk()
        ->assertJsonPath('data.plan.code', 'plan-a');
});
