<?php

declare(strict_types=1);

use App\Modules\HR\Models\Attendance;
use App\Modules\Organization\Models\Employee;

it('lets a manager record an attendance entry', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $employee = Employee::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson('/api/v1/attendance', [
        'employee_id' => $employee->id,
        'date' => '2026-06-02',
        'status' => 'absent',
        'note' => 'No show',
    ])->assertCreated()
        ->assertJsonPath('data.status', 'absent')
        ->assertJsonPath('data.recorded_by', $manager->id)
        ->assertJsonPath('data.worked_minutes', null);
});

it('recomputes lateness and worked minutes from supplied punches', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $employee = Employee::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson('/api/v1/attendance', [
        'employee_id' => $employee->id,
        'date' => '2026-06-02',
        'status' => 'present',
        'check_in_at' => '2026-06-02 09:30:00',
        'check_out_at' => '2026-06-02 16:00:00',
    ])->assertCreated()
        ->assertJsonPath('data.is_late', true)
        ->assertJsonPath('data.is_early_leave', true)
        ->assertJsonPath('data.worked_minutes', 390);
});

it('overrides an existing entry for the same employee and day', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $employee = Employee::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $payload = ['employee_id' => $employee->id, 'date' => '2026-06-02', 'status' => 'present'];
    $this->actingAs($manager, 'sanctum')->postJson('/api/v1/attendance', $payload)->assertCreated();

    $this->actingAs($manager, 'sanctum')->postJson('/api/v1/attendance', [...$payload, 'status' => 'half_day'])
        ->assertCreated()
        ->assertJsonPath('data.status', 'half_day');

    expect(Attendance::withoutGlobalScopes()->count())->toBe(1);
});

it('rejects recording for another tenant\'s employee', function (): void {
    $other = makeTenant(['slug' => 'rec-o']);
    $foreign = Employee::factory()->forTenant($other)->create();
    $tenant = makeTenant(['slug' => 'rec-m']);
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson('/api/v1/attendance', [
        'employee_id' => $foreign->id,
        'date' => '2026-06-02',
        'status' => 'present',
    ])->assertUnprocessable()->assertJsonValidationErrors('employee_id');
});

it('forbids staff from recording attendance', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    $employee = Employee::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/attendance', [
        'employee_id' => $employee->id,
        'date' => '2026-06-02',
        'status' => 'present',
    ])->assertForbidden();
});
