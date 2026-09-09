<?php

declare(strict_types=1);

use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Designation;

it('creates a designation under a department of the same tenant', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    $department = Department::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->postJson('/api/v1/designations', [
            'department_id' => $department->id,
            'title' => 'Sales Manager',
            'rank' => 2,
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Sales Manager')
        ->assertJsonPath('data.department.id', $department->id);
});

it('rejects a department_id from another tenant', function (): void {
    $other = makeTenant(['slug' => 'desig-other']);
    $foreignDept = Department::factory()->forTenant($other)->create();

    $tenant = makeTenant(['slug' => 'desig-mine']);
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->postJson('/api/v1/designations', [
            'department_id' => $foreignDept->id,
            'title' => 'Manager',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('department_id');
});

it('rejects a duplicate designation title within a tenant', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    Designation::factory()->forTenant($tenant)->create(['title' => 'Director']);
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->postJson('/api/v1/designations', ['title' => 'Director'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('title');
});
