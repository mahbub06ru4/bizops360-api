<?php

declare(strict_types=1);

use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Employee;

it('walks an employee through its full lifecycle', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    $department = Department::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($owner, 'sanctum');

    $id = $this->postJson('/api/v1/employees', [
        'department_id' => $department->id,
        'employee_code' => 'EMP-1',
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'hire_date' => '2024-01-15',
    ])->assertCreated()
        ->assertJsonPath('data.full_name', 'Ada Lovelace')
        ->assertJsonPath('data.employment_status', 'active')
        ->assertJsonPath('data.department.id', $department->id)
        ->json('data.id');

    $this->getJson("/api/v1/employees/{$id}")->assertOk();

    $this->putJson("/api/v1/employees/{$id}", [
        'employee_code' => 'EMP-1',
        'first_name' => 'Ada B.',
        'last_name' => 'Lovelace',
        'hire_date' => '2024-01-15',
    ])->assertOk()->assertJsonPath('data.first_name', 'Ada B.');

    $this->postJson("/api/v1/employees/{$id}/terminate")
        ->assertOk()
        ->assertJsonPath('data.employment_status', 'terminated');

    $this->deleteJson("/api/v1/employees/{$id}")->assertNoContent();
    expect(Employee::withoutGlobalScopes()->find($id))->toBeNull();
});

it('rejects a department that belongs to another tenant', function (): void {
    $other = makeTenant(['slug' => 'emp-other']);
    $foreignDept = Department::factory()->forTenant($other)->create();

    $tenant = makeTenant(['slug' => 'emp-mine']);
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->postJson('/api/v1/employees', [
            'department_id' => $foreignDept->id,
            'employee_code' => 'EMP-2',
            'first_name' => 'X',
            'last_name' => 'Y',
            'hire_date' => '2024-01-01',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('department_id');
});

it('lets staff read employees but not create them', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    Employee::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/employees')->assertOk();

    $this->actingAs($staff, 'sanctum')
        ->postJson('/api/v1/employees', [
            'employee_code' => 'EMP-3',
            'first_name' => 'X',
            'last_name' => 'Y',
            'hire_date' => '2024-01-01',
        ])
        ->assertForbidden();
});

it('hides another tenant\'s employee and 404s on direct access', function (): void {
    $tenantA = makeTenant(['slug' => 'emp-iso-a']);
    $owner = makeUser($tenantA, 'owner');

    $tenantB = makeTenant(['slug' => 'emp-iso-b']);
    $foreign = Employee::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')->getJson('/api/v1/employees')
        ->assertOk()->assertJsonPath('meta.total', 0);
    $this->actingAs($owner, 'sanctum')->getJson("/api/v1/employees/{$foreign->id}")->assertNotFound();
    $this->actingAs($owner, 'sanctum')->postJson("/api/v1/employees/{$foreign->id}/terminate")->assertNotFound();
});

it('paginates employees honouring a clamped per_page', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    Employee::factory()->forTenant($tenant)->count(5)->create();
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')->getJson('/api/v1/employees?per_page=2')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.total', 5)
        ->assertJsonPath('meta.last_page', 3);

    $this->actingAs($owner, 'sanctum')->getJson('/api/v1/employees?per_page=2&page=2')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.current_page', 2);

    // per_page is clamped to [1, 100] rather than trusted verbatim.
    $this->actingAs($owner, 'sanctum')->getJson('/api/v1/employees?per_page=0')
        ->assertOk()->assertJsonPath('meta.per_page', 1);
    $this->actingAs($owner, 'sanctum')->getJson('/api/v1/employees?per_page=9999')
        ->assertOk()->assertJsonPath('meta.per_page', 100);
});

it('searches employees by name, code, or email', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    $ada = Employee::factory()->forTenant($tenant)->create([
        'first_name' => 'Ada', 'last_name' => 'Lovelace', 'employee_code' => 'EMP-ADA', 'email' => 'ada@example.test',
    ]);
    Employee::factory()->forTenant($tenant)->create([
        'first_name' => 'Grace', 'last_name' => 'Hopper', 'employee_code' => 'EMP-GH', 'email' => 'grace@example.test',
    ]);
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')->getJson('/api/v1/employees?q=lovelace')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $ada->id);

    $this->actingAs($owner, 'sanctum')->getJson('/api/v1/employees?q=ada+love')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $ada->id);

    $this->actingAs($owner, 'sanctum')->getJson('/api/v1/employees?q=EMP-ADA')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $ada->id);

    $this->actingAs($owner, 'sanctum')->getJson('/api/v1/employees?q=ada@example.test')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $ada->id);

    $this->actingAs($owner, 'sanctum')->getJson('/api/v1/employees?q=nobody-matches-this')
        ->assertOk()->assertJsonCount(0, 'data');
});
