<?php

declare(strict_types=1);

use App\Modules\Finance\Domain\ExpenseStatus;
use App\Modules\Finance\Models\Expense;

it('lets a manager approve a pending expense', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $id = $this->actingAs($manager, 'sanctum')->postJson('/api/v1/expenses', [
        'category' => 'other',
        'title' => 'Taxi fare',
        'amount' => '25.00',
        'spent_on' => '2026-06-01',
        'method' => 'cash',
    ])->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->json('data.id');

    $this->actingAs($owner, 'sanctum')->postJson("/api/v1/expenses/{$id}/approve", [
        'note' => 'Looks good',
    ])->assertOk()
        ->assertJsonPath('data.status', 'approved')
        ->assertJsonPath('data.approved_by', $owner->id)
        ->assertJsonPath('data.decision_note', 'Looks good');

    expect(Expense::query()->findOrFail($id)->status)->toBe(ExpenseStatus::Approved);
});

it('lets a manager reject a pending expense', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $expense = Expense::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson("/api/v1/expenses/{$expense->id}/reject", [
        'note' => 'Missing receipt',
    ])->assertOk()
        ->assertJsonPath('data.status', 'rejected')
        ->assertJsonPath('data.decision_note', 'Missing receipt');
});

it('will not decide an expense twice', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $expense = Expense::factory()->forTenant($tenant)->status(ExpenseStatus::Approved)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson("/api/v1/expenses/{$expense->id}/approve")
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});

it('blocks staff from approving expenses', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    $expense = Expense::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->postJson("/api/v1/expenses/{$expense->id}/approve")
        ->assertForbidden();
});

it('404s approving another tenant\'s expense', function (): void {
    $tenantA = makeTenant(['slug' => 'exp-appr-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeTenant(['slug' => 'exp-appr-b']);
    $foreign = Expense::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson("/api/v1/expenses/{$foreign->id}/approve")
        ->assertNotFound();
});
