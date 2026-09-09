<?php

declare(strict_types=1);

use App\Modules\Operations\Models\Project;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Employee;

it('walks a project through its lifecycle', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    $department = Department::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($owner, 'sanctum');

    $id = $this->postJson('/api/v1/projects', [
        'name' => 'Website revamp',
        'code' => 'WEB',
        'department_id' => $department->id,
        'status' => 'active',
    ])->assertCreated()
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.department_id', $department->id)
        ->assertJsonPath('data.created_by', $owner->id)
        ->json('data.id');

    $this->getJson('/api/v1/projects')
        ->assertOk()
        ->assertJsonPath('data.0.id', $id)
        ->assertJsonPath('data.0.tasks_count', 0);

    $this->putJson("/api/v1/projects/{$id}", ['name' => 'Website', 'code' => 'WEB', 'status' => 'on_hold'])
        ->assertOk()
        ->assertJsonPath('data.status', 'on_hold');

    $this->deleteJson("/api/v1/projects/{$id}")->assertNoContent();
    expect(Project::withoutGlobalScopes()->count())->toBe(0);
});

it('validates and de-duplicates the project code', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    Project::factory()->forTenant($tenant)->create(['code' => 'DUP']);
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')->postJson('/api/v1/projects', [])
        ->assertUnprocessable()->assertJsonValidationErrors(['name', 'code']);

    $this->actingAs($owner, 'sanctum')->postJson('/api/v1/projects', ['name' => 'X', 'code' => 'DUP'])
        ->assertUnprocessable()->assertJsonValidationErrors('code');
});

it('rejects a lead employee from another tenant', function (): void {
    $other = makeTenant(['slug' => 'proj-o']);
    $foreign = Employee::factory()->forTenant($other)->create();
    $tenant = makeTenant(['slug' => 'proj-m']);
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')->postJson('/api/v1/projects', [
        'name' => 'X', 'code' => 'X1', 'lead_employee_id' => $foreign->id,
    ])->assertUnprocessable()->assertJsonValidationErrors('lead_employee_id');
});

it('lets staff view but not manage projects', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    Project::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/projects')->assertOk();
    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/projects', ['name' => 'X', 'code' => 'X1'])
        ->assertForbidden();
});

it('404s on another tenant\'s project', function (): void {
    $tenantA = makeTenant(['slug' => 'proj-iso-a']);
    $owner = makeUser($tenantA, 'owner');
    $tenantB = makeTenant(['slug' => 'proj-iso-b']);
    $foreign = Project::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')->getJson("/api/v1/projects/{$foreign->id}")->assertNotFound();
});
