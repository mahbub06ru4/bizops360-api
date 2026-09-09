<?php

declare(strict_types=1);

use App\Modules\CRM\Models\Customer;
use App\Modules\Finance\Models\Income;

it('walks an income entry through its lifecycle', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum');

    $id = $this->postJson('/api/v1/incomes', [
        'category' => 'other',
        'source' => 'Bank interest',
        'amount' => '125.50',
        'received_on' => '2026-06-01',
        'method' => 'bank_transfer',
    ])->assertCreated()
        ->assertJsonPath('data.amount', '125.50')
        ->assertJsonPath('data.category', 'other')
        ->assertJsonPath('data.recorded_by', $manager->id)
        ->json('data.id');

    $this->getJson('/api/v1/incomes')->assertOk()->assertJsonPath('data.0.id', $id);

    $this->putJson("/api/v1/incomes/{$id}", [
        'category' => 'other',
        'amount' => '200.00',
        'received_on' => '2026-06-02',
    ])->assertOk()->assertJsonPath('data.amount', '200.00');

    $this->deleteJson("/api/v1/incomes/{$id}")->assertForbidden();
    $this->actingAs($owner, 'sanctum')->deleteJson("/api/v1/incomes/{$id}")->assertNoContent();
});

it('validates the amount is present and positive', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson('/api/v1/incomes', [
        'amount' => '-5.00',
        'received_on' => '2026-06-01',
    ])->assertUnprocessable()->assertJsonValidationErrors('amount');
});

it('rejects a customer from another tenant', function (): void {
    $other = makeTenant(['slug' => 'inc-o']);
    $foreign = Customer::factory()->forTenant($other)->create();
    $tenant = makeTenant(['slug' => 'inc-m']);
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson('/api/v1/incomes', [
        'amount' => '10.00',
        'received_on' => '2026-06-01',
        'customer_id' => $foreign->id,
    ])->assertUnprocessable()->assertJsonValidationErrors('customer_id');
});

it('404s on another tenant\'s income entry', function (): void {
    $tenantA = makeTenant(['slug' => 'inc-iso-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeTenant(['slug' => 'inc-iso-b']);
    $foreign = Income::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson("/api/v1/incomes/{$foreign->id}")->assertNotFound();
    $this->actingAs($manager, 'sanctum')->putJson("/api/v1/incomes/{$foreign->id}", [
        'amount' => '1.00', 'received_on' => '2026-06-01',
    ])->assertNotFound();
});

it('does not leak another tenant\'s entries into the list', function (): void {
    $tenantA = makeTenant(['slug' => 'inc-list-a']);
    $manager = makeUser($tenantA, 'manager');
    Income::factory()->forTenant($tenantA)->count(2)->create();
    $tenantB = makeTenant(['slug' => 'inc-list-b']);
    Income::factory()->forTenant($tenantB)->count(3)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/incomes')
        ->assertOk()->assertJsonPath('meta.total', 2);
});
