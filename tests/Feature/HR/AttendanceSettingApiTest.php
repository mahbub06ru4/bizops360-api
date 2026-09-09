<?php

declare(strict_types=1);

use App\Modules\Organization\Models\Employee;
use Illuminate\Support\Carbon;

afterEach(function (): void {
    Carbon::setTestNow();
});

it('returns platform defaults until a schedule is configured', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/attendance-settings')
        ->assertOk()
        ->assertJsonPath('data.work_starts_at', '09:00')
        ->assertJsonPath('data.work_ends_at', '17:00')
        ->assertJsonPath('data.grace_minutes', 15);
});

it('lets a manager set the work schedule and it changes lateness', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $staff = makeUser($tenant, 'staff');
    Employee::factory()->forTenant($tenant)->create(['user_id' => $staff->id]);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->putJson('/api/v1/attendance-settings', [
        'work_starts_at' => '08:30',
        'work_ends_at' => '16:30',
        'grace_minutes' => 5,
    ])->assertOk()
        ->assertJsonPath('data.work_starts_at', '08:30')
        ->assertJsonPath('data.grace_minutes', 5);

    Carbon::setTestNow('2026-06-01 08:40:00');
    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/attendance/check-in')
        ->assertCreated()
        ->assertJsonPath('data.is_late', true);
});

it('lets staff view but not change the schedule', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/attendance-settings')->assertOk();
    $this->actingAs($staff, 'sanctum')->putJson('/api/v1/attendance-settings', [
        'work_starts_at' => '10:00',
        'work_ends_at' => '18:00',
        'grace_minutes' => 0,
    ])->assertForbidden();
});

it('validates the schedule payload', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->putJson('/api/v1/attendance-settings', [
        'work_starts_at' => '17:00',
        'work_ends_at' => '09:00',
        'grace_minutes' => 5,
    ])->assertUnprocessable()->assertJsonValidationErrors('work_ends_at');
});
