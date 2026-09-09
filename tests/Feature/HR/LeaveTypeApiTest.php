<?php

declare(strict_types=1);

use App\Modules\HR\Models\LeaveRequest;
use App\Modules\HR\Models\LeaveType;

it('creates and updates a leave type', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum');

    $id = $this->postJson('/api/v1/leave-types', [
        'name' => 'Annual Leave',
        'code' => 'ANNUAL',
        'default_days_per_year' => 24,
    ])->assertCreated()
        ->assertJsonPath('data.default_days_per_year', 24)
        ->assertJsonPath('data.is_paid', true)
        ->json('data.id');

    $this->putJson("/api/v1/leave-types/{$id}", [
        'name' => 'Annual Leave',
        'code' => 'ANNUAL',
        'default_days_per_year' => 24,
        'requires_approval' => false,
    ])->assertOk()->assertJsonPath('data.requires_approval', false);
});

it('blocks deleting a leave type that has requests', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    $type = LeaveType::factory()->forTenant($tenant)->create();
    LeaveRequest::factory()->forTenant($tenant)->create(['leave_type_id' => $type->id]);
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->deleteJson("/api/v1/leave-types/{$type->id}")
        ->assertUnprocessable();
});

it('lets staff view but not manage leave types', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/leave-types')->assertOk();
    $this->actingAs($staff, 'sanctum')
        ->postJson('/api/v1/leave-types', ['name' => 'X', 'code' => 'X'])
        ->assertForbidden();
});
