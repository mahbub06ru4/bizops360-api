<?php

declare(strict_types=1);

it('returns the role catalogue to a permitted user', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->getJson('/api/v1/roles')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'owner')
        ->assertJsonFragment(['name' => 'staff'])
        ->assertJsonPath('data.0.permissions', fn ($permissions) => in_array('user.assign_roles', $permissions, true));
});

it('hides the role catalogue from a user without role.view', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/roles')->assertForbidden();
});
