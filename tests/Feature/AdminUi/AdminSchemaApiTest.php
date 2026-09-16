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
        ->assertJsonCount(19, 'data.resources');

    $resourceKeys = array_column($response->json('data.resources'), 'key');

    expect($resourceKeys)->toEqual([
        'branches', 'departments', 'designations', 'employees', 'teams', 'users',
        'holidays', 'leave_types', 'leave_requests', 'leave_balances', 'attendance',
        'projects', 'tasks',
        'leads', 'customers', 'follow_ups',
        'incomes', 'expenses', 'invoices',
    ]);

    // Resources without a matching backend route expose a null permission
    // instead of one that would just never be satisfiable.
    $byKey = collect($response->json('data.resources'))->keyBy('key');
    expect($byKey['leave_requests']['permissions']['update'])->toBeNull();
    expect($byKey['leave_balances']['permissions']['create'])->toBeNull();
    expect($byKey['attendance']['permissions']['delete'])->toBeNull();
    expect($byKey['follow_ups']['permissions']['create'])->toBeNull();
    expect($byKey['follow_ups']['permissions']['update'])->toBe('follow_up.update');
});

it('requires authentication', function (): void {
    $this->getJson('/api/v1/admin/schema')->assertUnauthorized();
});
