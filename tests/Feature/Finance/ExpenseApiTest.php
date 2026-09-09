<?php

declare(strict_types=1);

use App\Modules\Finance\Models\Expense;
use App\Modules\Organization\Models\Employee;

it('walks an expense through its lifecycle', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum');

    $id = $this->postJson('/api/v1/expenses', [
        'category' => 'supplier',
        'supplier_name' => 'Acme Supplies',
        'title' => 'Stationery',
        'amount' => '89.99',
        'spent_on' => '2026-06-01',
        'method' => 'card',
    ])->assertCreated()
        ->assertJsonPath('data.category', 'supplier')
        ->assertJsonPath('data.amount', '89.99')
        ->json('data.id');

    $this->getJson('/api/v1/expenses')->assertOk()->assertJsonPath('data.0.id', $id);

    $this->putJson("/api/v1/expenses/{$id}", [
        'category' => 'office',
        'title' => 'Stationery and toner',
        'amount' => '95.00',
        'spent_on' => '2026-06-02',
    ])->assertOk()->assertJsonPath('data.category', 'office');

    $this->deleteJson("/api/v1/expenses/{$id}")->assertForbidden();
    $this->actingAs($owner, 'sanctum')->deleteJson("/api/v1/expenses/{$id}")->assertNoContent();
});

it('rejects an employee from another tenant', function (): void {
    $other = makeTenant(['slug' => 'exp-o']);
    $foreign = Employee::factory()->forTenant($other)->create();
    $tenant = makeTenant(['slug' => 'exp-m']);
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson('/api/v1/expenses', [
        'category' => 'employee',
        'title' => 'Travel reimbursement',
        'amount' => '40.00',
        'spent_on' => '2026-06-01',
        'employee_id' => $foreign->id,
    ])->assertUnprocessable()->assertJsonValidationErrors('employee_id');
});

it('validates the title and amount', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson('/api/v1/expenses', [
        'amount' => '0',
        'spent_on' => '2026-06-01',
    ])->assertUnprocessable()->assertJsonValidationErrors(['title', 'amount']);
});

it('404s on another tenant\'s expense', function (): void {
    $tenantA = makeTenant(['slug' => 'exp-iso-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeTenant(['slug' => 'exp-iso-b']);
    $foreign = Expense::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson("/api/v1/expenses/{$foreign->id}")->assertNotFound();
});
