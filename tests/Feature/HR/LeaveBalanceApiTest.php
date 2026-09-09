<?php

declare(strict_types=1);

use App\Modules\HR\Models\LeaveBalance;
use App\Modules\HR\Models\LeaveType;
use App\Modules\Organization\Models\Employee;

it('lets a manager set and list a leave balance', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $employee = Employee::factory()->forTenant($tenant)->create();
    $type = LeaveType::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->putJson('/api/v1/leave-balances', [
        'employee_id' => $employee->id,
        'leave_type_id' => $type->id,
        'year' => 2026,
        'entitled_days' => 18,
    ])->assertOk()
        ->assertJsonPath('data.entitled_days', 18)
        ->assertJsonPath('data.remaining_days', 18);

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/leave-balances')
        ->assertOk()
        ->assertJsonPath('meta.total', 1);
});

it('updates the entitlement without touching used days', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $balance = LeaveBalance::factory()->forTenant($tenant)->create(['used_days' => 5, 'entitled_days' => 20]);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->putJson('/api/v1/leave-balances', [
        'employee_id' => $balance->employee_id,
        'leave_type_id' => $balance->leave_type_id,
        'year' => $balance->year,
        'entitled_days' => 25,
    ])->assertOk()
        ->assertJsonPath('data.entitled_days', 25)
        ->assertJsonPath('data.used_days', 5);
});

it('forbids a staff member from setting balances and scopes their list', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    $ownEmployee = Employee::factory()->forTenant($tenant)->create(['user_id' => $staff->id]);
    LeaveBalance::factory()->forTenant($tenant)->create(['employee_id' => $ownEmployee->id]);
    LeaveBalance::factory()->forTenant($tenant)->create();
    $type = LeaveType::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->putJson('/api/v1/leave-balances', [
        'employee_id' => $ownEmployee->id,
        'leave_type_id' => $type->id,
        'year' => 2026,
        'entitled_days' => 10,
    ])->assertForbidden();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/leave-balances')
        ->assertOk()
        ->assertJsonPath('meta.total', 1);
});

it('rejects setting a balance for another tenant\'s employee', function (): void {
    $other = makeTenant(['slug' => 'lb-other']);
    $foreignEmployee = Employee::factory()->forTenant($other)->create();

    $tenant = makeTenant(['slug' => 'lb-mine']);
    $manager = makeUser($tenant, 'manager');
    $type = LeaveType::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->putJson('/api/v1/leave-balances', [
        'employee_id' => $foreignEmployee->id,
        'leave_type_id' => $type->id,
        'year' => 2026,
        'entitled_days' => 10,
    ])->assertUnprocessable()->assertJsonValidationErrors('employee_id');
});
