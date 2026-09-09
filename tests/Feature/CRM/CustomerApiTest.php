<?php

declare(strict_types=1);

use App\Modules\CRM\Models\Customer;
use App\Modules\Organization\Models\Employee;

it('walks a customer through its lifecycle', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum');

    $id = $this->postJson('/api/v1/customers', [
        'name' => 'Globex Ltd',
        'type' => 'business',
        'email' => 'ap@globex.test',
    ])->assertCreated()
        ->assertJsonPath('data.type', 'business')
        ->assertJsonPath('data.created_by', $manager->id)
        ->json('data.id');

    $this->getJson('/api/v1/customers')->assertOk()->assertJsonPath('data.0.id', $id);

    $this->putJson("/api/v1/customers/{$id}", ['name' => 'Globex', 'type' => 'business', 'phone' => '555-1000'])
        ->assertOk()->assertJsonPath('data.phone', '555-1000');

    $this->deleteJson("/api/v1/customers/{$id}")->assertNoContent();
});

it('rejects an owner employee from another tenant', function (): void {
    $other = makeTenant(['slug' => 'cust-o']);
    $foreign = Employee::factory()->forTenant($other)->create();
    $tenant = makeTenant(['slug' => 'cust-m']);
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson('/api/v1/customers', [
        'name' => 'X', 'owner_employee_id' => $foreign->id,
    ])->assertUnprocessable()->assertJsonValidationErrors('owner_employee_id');
});

it('scopes customers to involvement unless the caller may view all', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $staff = makeUser($tenant, 'staff');
    $staffEmployee = Employee::factory()->forTenant($tenant)->create(['user_id' => $staff->id]);
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/customers', ['name' => 'Mine'])->assertCreated();
    Customer::factory()->forTenant($tenant)->create(['owner_employee_id' => $staffEmployee->id]);
    Customer::factory()->forTenant($tenant)->create();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/customers')->assertOk()->assertJsonPath('meta.total', 2);
    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/customers')->assertOk()->assertJsonPath('meta.total', 3);
});

it('404s on another tenant\'s customer', function (): void {
    $tenantA = makeTenant(['slug' => 'cust-iso-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeTenant(['slug' => 'cust-iso-b']);
    $foreign = Customer::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson("/api/v1/customers/{$foreign->id}")->assertNotFound();
});
