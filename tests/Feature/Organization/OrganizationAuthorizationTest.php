<?php

declare(strict_types=1);

use App\Modules\Organization\Models\Branch;

it('lets staff read but not create branches', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    Branch::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/branches')->assertOk();

    $this->actingAs($staff, 'sanctum')
        ->postJson('/api/v1/branches', ['name' => 'X', 'code' => 'X1'])
        ->assertForbidden();
});

it('lets a manager create and update but not delete a branch', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $branch = Branch::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')
        ->putJson("/api/v1/branches/{$branch->id}", ['name' => 'Updated', 'code' => $branch->code])
        ->assertOk();

    $this->actingAs($manager, 'sanctum')
        ->deleteJson("/api/v1/branches/{$branch->id}")
        ->assertForbidden();
});

it('grants the owner every organization action', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    $branch = Branch::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->deleteJson("/api/v1/branches/{$branch->id}")
        ->assertNoContent();
});
