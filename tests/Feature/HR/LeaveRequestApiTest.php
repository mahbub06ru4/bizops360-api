<?php

declare(strict_types=1);

use App\Modules\HR\Models\Holiday;
use App\Modules\HR\Models\LeaveBalance;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\HR\Models\LeaveType;
use App\Modules\Organization\Models\Employee;

/**
 * @param  array<string, mixed>  $typeState
 * @return array<string, mixed>
 */
function hrLeaveFixture(array $typeState = []): array
{
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $staff = makeUser($tenant, 'staff');
    $employee = Employee::factory()->forTenant($tenant)->create(['user_id' => $staff->id]);
    $type = LeaveType::factory()->forTenant($tenant)->create([
        'default_days_per_year' => 20,
        'is_paid' => true,
        'requires_approval' => true,
        ...$typeState,
    ]);
    clearTenantContext();

    return compact('tenant', 'staff', 'employee', 'manager', 'type');
}

it('lets a staff member request leave for themselves and a manager approve it', function (): void {
    ['staff' => $staff, 'employee' => $employee, 'manager' => $manager, 'type' => $type] = hrLeaveFixture();

    $id = $this->actingAs($staff, 'sanctum')->postJson('/api/v1/leave-requests', [
        'leave_type_id' => $type->id,
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-03',
        'reason' => 'Trip',
    ])->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.days', 3)
        ->assertJsonPath('data.employee_id', $employee->id)
        ->json('data.id');

    $this->actingAs($manager, 'sanctum')
        ->postJson("/api/v1/leave-requests/{$id}/approve", ['note' => 'ok'])
        ->assertOk()
        ->assertJsonPath('data.status', 'approved');

    $balance = LeaveBalance::withoutGlobalScopes()
        ->where('employee_id', $employee->id)->where('leave_type_id', $type->id)->firstOrFail();
    expect($balance->used_days)->toBe(3)->and($balance->entitled_days)->toBe(20);
});

it('auto-approves and charges the balance when the type does not require approval', function (): void {
    ['staff' => $staff, 'employee' => $employee, 'type' => $type] = hrLeaveFixture(['requires_approval' => false]);

    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/leave-requests', [
        'leave_type_id' => $type->id,
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-02',
    ])->assertCreated()->assertJsonPath('data.status', 'approved');

    $balance = LeaveBalance::withoutGlobalScopes()
        ->where('employee_id', $employee->id)->firstOrFail();
    expect($balance->used_days)->toBe(2);
});

it('excludes holidays from the chargeable day count', function (): void {
    ['tenant' => $tenant, 'staff' => $staff, 'type' => $type] = hrLeaveFixture(['requires_approval' => false]);
    Holiday::factory()->forTenant($tenant)->create(['date' => '2026-06-02']);

    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/leave-requests', [
        'leave_type_id' => $type->id,
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-03',
    ])->assertCreated()->assertJsonPath('data.days', 2);
});

it('rejects a request that would exceed the entitlement', function (): void {
    ['staff' => $staff, 'employee' => $employee, 'type' => $type, 'manager' => $manager] =
        hrLeaveFixture(['requires_approval' => false]);

    $this->actingAs($manager, 'sanctum')->putJson('/api/v1/leave-balances', [
        'employee_id' => $employee->id,
        'leave_type_id' => $type->id,
        'year' => 2026,
        'entitled_days' => 2,
    ])->assertOk();

    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/leave-requests', [
        'leave_type_id' => $type->id,
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-03',
    ])->assertUnprocessable();

    expect(LeaveRequest::withoutGlobalScopes()->count())->toBe(0);
});

it('restores the balance when an approved request is cancelled', function (): void {
    ['staff' => $staff, 'employee' => $employee, 'type' => $type] = hrLeaveFixture(['requires_approval' => false]);

    $id = $this->actingAs($staff, 'sanctum')->postJson('/api/v1/leave-requests', [
        'leave_type_id' => $type->id,
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-03',
    ])->json('data.id');

    $this->actingAs($staff, 'sanctum')->postJson("/api/v1/leave-requests/{$id}/cancel")
        ->assertOk()->assertJsonPath('data.status', 'cancelled');

    $balance = LeaveBalance::withoutGlobalScopes()->where('employee_id', $employee->id)->firstOrFail();
    expect($balance->used_days)->toBe(0);
});

it('forbids a staff member from approving leave', function (): void {
    ['staff' => $staff, 'type' => $type] = hrLeaveFixture();

    $id = LeaveRequest::factory()->forTenant($type->tenant)->create([
        'leave_type_id' => $type->id,
    ])->id;

    $this->actingAs($staff, 'sanctum')->postJson("/api/v1/leave-requests/{$id}/approve")->assertForbidden();
});

it('only shows a staff member their own leave requests', function (): void {
    ['tenant' => $tenant, 'staff' => $staff, 'employee' => $employee, 'type' => $type] = hrLeaveFixture();
    LeaveRequest::factory()->forTenant($tenant)->create(['employee_id' => $employee->id, 'leave_type_id' => $type->id]);
    $otherEmployee = Employee::factory()->forTenant($tenant)->create();
    LeaveRequest::factory()->forTenant($tenant)->create(['employee_id' => $otherEmployee->id, 'leave_type_id' => $type->id]);

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/leave-requests')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.employee_id', $employee->id);
});

it('404s approving another tenant\'s leave request', function (): void {
    ['manager' => $manager] = hrLeaveFixture();

    $otherTenant = makeTenant(['slug' => 'lr-other']);
    $foreign = LeaveRequest::factory()->forTenant($otherTenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson("/api/v1/leave-requests/{$foreign->id}/approve")->assertNotFound();
});
