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
