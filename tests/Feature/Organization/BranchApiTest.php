<?php

declare(strict_types=1);

use App\Modules\Organization\Models\Branch;

it('walks a branch through its full lifecycle', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum');

    $id = $this->postJson('/api/v1/branches', [
        'name' => 'Head Office',
        'code' => 'HO',
        'is_head_office' => true,
    ])->assertCreated()
        ->assertJsonPath('data.name', 'Head Office')
        ->assertJsonPath('data.is_head_office', true)
        ->json('data.id');

    $this->getJson('/api/v1/branches')
        ->assertOk()
        ->assertJsonPath('data.0.id', $id)
        ->assertJsonStructure(['data', 'links', 'meta']);

    $this->getJson("/api/v1/branches/{$id}")
        ->assertOk()
        ->assertJsonPath('data.code', 'HO');

    $this->putJson("/api/v1/branches/{$id}", ['name' => 'HQ', 'code' => 'HO'])
        ->assertOk()
        ->assertJsonPath('data.name', 'HQ');

    $this->deleteJson("/api/v1/branches/{$id}")->assertNoContent();

    expect(Branch::withoutGlobalScopes()->find($id))->toBeNull();
});

it('validates branch input', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->postJson('/api/v1/branches', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'code']);
});

it('rejects a duplicate branch code within the same tenant', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    Branch::factory()->forTenant($tenant)->create(['code' => 'DUP']);
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->postJson('/api/v1/branches', ['name' => 'Another', 'code' => 'DUP'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});

it('allows the same branch code across different tenants', function (): void {
    $tenantA = makeTenant(['slug' => 'code-a']);
    Branch::factory()->forTenant($tenantA)->create(['code' => 'SHARED']);

    $tenantB = makeTenant(['slug' => 'code-b']);
    $owner = makeUser($tenantB, 'owner');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->postJson('/api/v1/branches', ['name' => 'B Branch', 'code' => 'SHARED'])
        ->assertCreated();
});

it('requires authentication', function (): void {
    $this->getJson('/api/v1/branches')->assertUnauthorized();
});
