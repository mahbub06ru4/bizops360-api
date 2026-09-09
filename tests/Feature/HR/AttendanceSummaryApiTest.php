<?php

declare(strict_types=1);

use App\Modules\HR\Domain\AttendanceStatus;
use App\Modules\HR\Models\Attendance;
use App\Modules\Organization\Models\Employee;

it('summarises a month for one employee', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $employee = Employee::factory()->forTenant($tenant)->create();

    Attendance::factory()->forTenant($tenant)->create([
        'employee_id' => $employee->id, 'date' => '2026-06-01',
        'status' => AttendanceStatus::Present, 'worked_minutes' => 480,
    ]);
    Attendance::factory()->forTenant($tenant)->create([
        'employee_id' => $employee->id, 'date' => '2026-06-02',
        'status' => AttendanceStatus::Late, 'worked_minutes' => 420, 'is_late' => true,
    ]);
    Attendance::factory()->forTenant($tenant)->create([
        'employee_id' => $employee->id, 'date' => '2026-06-03',
        'status' => AttendanceStatus::Absent, 'worked_minutes' => null,
        'check_in_at' => null, 'check_out_at' => null,
    ]);
    Attendance::factory()->forTenant($tenant)->create([
        'employee_id' => $employee->id, 'date' => '2026-05-31',
        'status' => AttendanceStatus::Present, 'worked_minutes' => 480,
    ]);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')
        ->getJson("/api/v1/attendance/summary?month=2026-06&employee_id={$employee->id}")
        ->assertOk()
        ->assertJsonPath('data.month', '2026-06')
        ->assertJsonPath('data.employee_id', $employee->id)
        ->assertJsonPath('data.days_recorded', 3)
        ->assertJsonPath('data.present', 1)
        ->assertJsonPath('data.late', 1)
        ->assertJsonPath('data.absent', 1)
        ->assertJsonPath('data.late_count', 1)
        ->assertJsonPath('data.worked_minutes', 900);
});

it('defaults the summary to the caller\'s own employee record', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    $employee = Employee::factory()->forTenant($tenant)->create(['user_id' => $staff->id]);
    Attendance::factory()->forTenant($tenant)->create([
        'employee_id' => $employee->id, 'date' => '2026-06-01',
        'status' => AttendanceStatus::Present, 'worked_minutes' => 480,
    ]);
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/attendance/summary?month=2026-06')
        ->assertOk()
        ->assertJsonPath('data.employee_id', $employee->id)
        ->assertJsonPath('data.present', 1);
});
