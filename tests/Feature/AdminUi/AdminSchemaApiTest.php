<?php

declare(strict_types=1);

it('returns the admin panel schema for an authenticated tenant user', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $response = $this->actingAs($owner, 'sanctum')->getJson('/api/v1/admin/schema')
        ->assertOk()
        ->assertJsonPath('data.modules.0.key', 'organization')
        ->assertJsonPath('data.resources.0.key', 'branches')
        ->assertJsonPath('data.resources.3.key', 'employees')
        ->assertJsonPath('data.resources.4.key', 'teams')
        ->assertJsonCount(5, 'data.modules')
        ->assertJsonCount(14, 'data.resources');

    $resourceKeys = array_column($response->json('data.resources'), 'key');

    expect($resourceKeys)->toEqual([
        'branches', 'departments', 'designations', 'employees', 'teams',
        'holidays', 'leave_types',
        'projects', 'tasks',
        'leads', 'customers',
        'incomes', 'expenses', 'invoices',
    ]);
});

it('requires authentication', function (): void {
    $this->getJson('/api/v1/admin/schema')->assertUnauthorized();
});
