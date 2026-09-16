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
        ->assertJsonCount(3, 'data.dashboards')
        ->assertJsonCount(23, 'data.resources');

    $resourceKeys = array_column($response->json('data.resources'), 'key');

    expect($resourceKeys)->toEqual([
        'branches', 'departments', 'designations', 'employees', 'teams', 'users', 'roles',
        'holidays', 'leave_types', 'leave_requests', 'leave_balances', 'attendance',
        'attendance_settings', 'office_location', 'employee_documents',
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
    expect($byKey['roles']['permissions']['create'])->toBeNull();
    expect($byKey['roles']['paginated'])->toBeFalse();
    expect($byKey['attendance_settings']['mode'])->toBe('singleton');
    expect($byKey['office_location']['mode'])->toBe('singleton');
    expect($byKey['employee_documents']['permissions']['update'])->toBeNull();

    $dashboardKeys = array_column($response->json('data.dashboards'), 'key');
    expect($dashboardKeys)->toEqual(['operations_overview', 'crm_reports', 'finance_reports']);
});

it('requires authentication', function (): void {
    $this->getJson('/api/v1/admin/schema')->assertUnauthorized();
});
