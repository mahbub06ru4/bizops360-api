<?php

declare(strict_types=1);

it('returns the admin panel schema for an authenticated tenant user', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')->getJson('/api/v1/admin/schema')
        ->assertOk()
        ->assertJsonPath('data.modules.0.key', 'organization')
        ->assertJsonPath('data.resources.0.key', 'branches')
        ->assertJsonPath('data.resources.3.key', 'employees');
});

it('requires authentication', function (): void {
    $this->getJson('/api/v1/admin/schema')->assertUnauthorized();
});
