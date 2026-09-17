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

    // Row-level actions beyond plain CRUD (approve/reject, convert,
    // terminate, ...) and the other action-engine capabilities.
    expect(array_column($byKey['expenses']['actions'], 'key'))->toEqual(['approve', 'reject']);
    expect(array_column($byKey['leave_requests']['actions'], 'key'))->toEqual(['approve', 'reject', 'cancel']);
    expect(array_column($byKey['invoices']['actions'], 'key'))->toEqual(['send', 'void', 'record_payment', 'refund']);
    expect(array_column($byKey['employees']['actions'], 'key'))->toEqual(['terminate']);
    expect(array_column($byKey['tasks']['actions'], 'key'))->toEqual(['update_status', 'update_assignee']);
    expect(array_column($byKey['follow_ups']['actions'], 'key'))->toEqual(['complete', 'cancel']);
    expect(array_column($byKey['leads']['actions'], 'key'))->toEqual(['move_stage', 'convert']);
    expect($byKey['teams']['actions'][0]['fetchDetail'])->toBeTrue();
    expect($byKey['users']['actions'][0]['fields'][0]['prefillFrom'])->toBe('roles');
    expect($byKey['employee_documents']['columns'][5]['link'])->toBeTrue();
    expect($byKey['attendance']['summaryEndpoint'])->toBe('/attendance/summary');
    expect($byKey['leave_balances']['actions'][0]['scope'])->toBe('resource');
    expect($byKey['leave_balances']['actions'][0]['endpoint'])->toBe('/leave-balances');

    // Generic detail pages: relatedLists (nested CRUD), embeddedLists
    // (read-only, sourced from the parent's own GET {id} response), and
    // an activity timeline with a note form.
    expect(array_column($byKey['leads']['detail']['relatedLists'], 'key'))->toEqual(['contacts', 'follow_ups']);
    expect($byKey['leads']['detail']['relatedLists'][0]['listEndpoint'])->toBe('/leads/{id}/contacts');
    expect($byKey['leads']['detail']['relatedLists'][0]['rowEndpoint'])->toBe('/contacts/{id}');
    expect($byKey['leads']['detail']['relatedLists'][1]['permissions']['update'])->toBe('follow_up.update');
    expect(array_column($byKey['leads']['detail']['relatedLists'][1]['actions'], 'key'))->toEqual(['complete', 'cancel']);
    expect($byKey['leads']['detail']['activity']['listEndpoint'])->toBe('/leads/{id}/activities');
    expect($byKey['leads']['detail']['activity']['noteEndpoint'])->toBe('/leads/{id}/notes');
    expect($byKey['customers']['detail']['relatedLists'][0]['listEndpoint'])->toBe('/customers/{id}/contacts');

    expect(array_column($byKey['tasks']['detail']['relatedLists'], 'key'))->toEqual(['comments', 'attachments']);
    expect($byKey['tasks']['detail']['relatedLists'][0]['ownerField'])->toBe('author_id');
    expect($byKey['tasks']['detail']['relatedLists'][0]['bypassPermission'])->toBe('task.view_all');
    expect($byKey['tasks']['detail']['relatedLists'][1]['columns'][2]['link'])->toBeTrue();

    expect(array_column($byKey['invoices']['detail']['embeddedLists'], 'key'))->toEqual(['payments', 'refunds']);
    expect($byKey['invoices']['detail']['embeddedLists'][0]['path'])->toBe('payments');
    expect($byKey['invoices']['detail']['embeddedLists'][1]['path'])->toBe('refunds');

    $dashboardKeys = array_column($response->json('data.dashboards'), 'key');
    expect($dashboardKeys)->toEqual(['operations_overview', 'crm_reports', 'finance_reports']);

    $charts = collect($response->json('data.charts'))->keyBy('key');
    expect($charts->keys()->all())->toEqual(['finance_trend', 'tasks_by_status', 'projects_by_status', 'leads_by_stage']);
    expect($charts['finance_trend']['type'])->toBe('area');
    expect($charts['finance_trend']['xKey'])->toBe('label');
    expect(array_column($charts['finance_trend']['series'], 'key'))->toEqual(['income', 'expense']);
    expect($charts['tasks_by_status']['dataPath'])->toBe('tasks.by_status');
    expect($charts['tasks_by_status']['endpoint'])->toBe('/operations/overview');
    expect($charts['projects_by_status']['endpoint'])->toBe('/operations/overview');
    expect($charts['leads_by_stage']['type'])->toBe('bar');
});

it('requires authentication', function (): void {
    $this->getJson('/api/v1/admin/schema')->assertUnauthorized();
});
