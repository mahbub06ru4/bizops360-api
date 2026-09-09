<?php

declare(strict_types=1);

use App\Modules\Organization\Models\Branch;
use App\Modules\Organization\Models\Department;

it('creates a department under a branch of the same tenant', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    $branch = Branch::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->postJson('/api/v1/departments', [
            'branch_id' => $branch->id,
            'name' => 'Sales',
            'code' => 'SALES',
        ])
        ->assertCreated()
        ->assertJsonPath('data.branch_id', $branch->id)
        ->assertJsonPath('data.branch.id', $branch->id);
});

it('rejects a branch_id that belongs to another tenant', function (): void {
    $other = makeTenant(['slug' => 'dept-other']);
    $foreignBranch = Branch::factory()->forTenant($other)->create();

    $tenant = makeTenant(['slug' => 'dept-mine']);
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->postJson('/api/v1/departments', [
            'branch_id' => $foreignBranch->id,
            'name' => 'Sales',
            'code' => 'SALES',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('branch_id');
});

it('updates and deletes a department', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    $department = Department::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->putJson("/api/v1/departments/{$department->id}", ['name' => 'Renamed', 'code' => $department->code])
        ->assertOk()
        ->assertJsonPath('data.name', 'Renamed');

    $this->actingAs($owner, 'sanctum')
        ->deleteJson("/api/v1/departments/{$department->id}")
        ->assertNoContent();
});
