<?php

declare(strict_types=1);

use App\Modules\HR\Models\Attendance;
use App\Modules\Organization\Models\Employee;
use Illuminate\Support\Carbon;

afterEach(function (): void {
    Carbon::setTestNow();
});

it('checks in on time then out, computing worked minutes', function (): void {
    Carbon::setTestNow('2026-06-01 08:55:00');
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    Employee::factory()->forTenant($tenant)->create(['user_id' => $staff->id]);
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/attendance/check-in')
        ->assertCreated()
        ->assertJsonPath('data.status', 'present')
        ->assertJsonPath('data.is_late', false);

    Carbon::setTestNow('2026-06-01 17:30:00');

    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/attendance/check-out')
        ->assertOk()
        ->assertJsonPath('data.is_early_leave', false)
        ->assertJsonPath('data.worked_minutes', 515);
});

it('flags a late check-in', function (): void {
    Carbon::setTestNow('2026-06-01 09:45:00');
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    Employee::factory()->forTenant($tenant)->create(['user_id' => $staff->id]);
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/attendance/check-in')
        ->assertCreated()
        ->assertJsonPath('data.status', 'late')
        ->assertJsonPath('data.is_late', true);
});

it('rejects a second check-in on the same day', function (): void {
    Carbon::setTestNow('2026-06-01 09:00:00');
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    Employee::factory()->forTenant($tenant)->create(['user_id' => $staff->id]);
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/attendance/check-in')->assertCreated();
    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/attendance/check-in')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('check_in');
});

it('rejects a check-out with no check-in', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    Employee::factory()->forTenant($tenant)->create(['user_id' => $staff->id]);
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/attendance/check-out')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('check_out');
});

it('rejects check-in for a user with no employee record', function (): void {
    $tenant = makeTenant();
    $user = makeUser($tenant, 'staff');
    clearTenantContext();

    $this->actingAs($user, 'sanctum')->postJson('/api/v1/attendance/check-in')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('employee');
});

it('scopes the attendance list to the caller unless they may view all', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    $manager = makeUser($tenant, 'manager');
    $mine = Employee::factory()->forTenant($tenant)->create(['user_id' => $staff->id]);
    $other = Employee::factory()->forTenant($tenant)->create();
    Attendance::factory()->forTenant($tenant)->create(['employee_id' => $mine->id, 'date' => '2026-06-01']);
    Attendance::factory()->forTenant($tenant)->create(['employee_id' => $other->id, 'date' => '2026-06-01']);
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/attendance')
        ->assertOk()->assertJsonPath('meta.total', 1);

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/attendance')
        ->assertOk()->assertJsonPath('meta.total', 2);
});

it('404s on another tenant\'s attendance record', function (): void {
    $tenantA = makeTenant(['slug' => 'att-iso-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeTenant(['slug' => 'att-iso-b']);
    $foreign = Attendance::factory()->forTenant($tenantB)->create(['date' => '2026-06-01']);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson("/api/v1/attendance/{$foreign->id}")->assertNotFound();
});
