<?php

declare(strict_types=1);

use App\Modules\CRM\Models\CrmActivity;
use App\Modules\CRM\Models\FollowUp;
use App\Modules\CRM\Models\Lead;
use App\Modules\Organization\Models\Employee;
use Illuminate\Support\Carbon;

afterEach(fn () => Carbon::setTestNow());

it('schedules, lists, completes and cancels follow-ups', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $employee = Employee::factory()->forTenant($tenant)->create();
    $lead = Lead::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum');

    $id = $this->postJson("/api/v1/leads/{$lead->id}/follow-ups", [
        'type' => 'call',
        'assigned_employee_id' => $employee->id,
        'due_at' => '2026-07-01 10:00:00',
        'notes' => 'Discuss pricing',
    ])->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.assigned_employee_id', $employee->id)
        ->json('data.id');

    $this->getJson("/api/v1/leads/{$lead->id}/follow-ups")->assertOk()->assertJsonPath('meta.total', 1);
    $this->getJson('/api/v1/follow-ups')->assertOk()->assertJsonPath('meta.total', 1);

    expect(CrmActivity::withoutGlobalScopes()->where('event', 'follow_up_scheduled')->count())->toBe(1);

    $this->postJson("/api/v1/follow-ups/{$id}/complete", ['outcome' => 'Agreed on tier 2'])
        ->assertOk()
        ->assertJsonPath('data.status', 'completed')
        ->assertJsonPath('data.outcome', 'Agreed on tier 2');

    expect(CrmActivity::withoutGlobalScopes()->where('event', 'follow_up_completed')->count())->toBe(1);

    $this->postJson("/api/v1/follow-ups/{$id}/complete", ['outcome' => 'again'])->assertUnprocessable();
});

it('will not edit a resolved follow-up', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $lead = Lead::factory()->forTenant($tenant)->create();
    $followUp = FollowUp::factory()->forTenant($tenant)->attachedTo($lead)->create(['status' => 'cancelled']);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->putJson("/api/v1/follow-ups/{$followUp->id}", [
        'type' => 'email', 'due_at' => '2026-07-02 09:00:00',
    ])->assertUnprocessable()->assertJsonValidationErrors('status');
});

it('filters follow-ups by overdue and scopes to involvement', function (): void {
    Carbon::setTestNow('2026-06-15 09:00:00');
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $staff = makeUser($tenant, 'staff');
    $staffEmp = Employee::factory()->forTenant($tenant)->create(['user_id' => $staff->id]);
    $lead = Lead::factory()->forTenant($tenant)->create();

    FollowUp::factory()->forTenant($tenant)->attachedTo($lead)->create([
        'assigned_employee_id' => $staffEmp->id, 'due_at' => '2026-06-14 09:00:00',
    ]);
    FollowUp::factory()->forTenant($tenant)->attachedTo($lead)->create([
        'assigned_employee_id' => $staffEmp->id, 'due_at' => '2026-06-20 09:00:00',
    ]);
    FollowUp::factory()->forTenant($tenant)->attachedTo($lead)->create(['due_at' => '2026-06-14 09:00:00']);
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/follow-ups')
        ->assertOk()->assertJsonPath('meta.total', 2);
    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/follow-ups?overdue=1')
        ->assertOk()->assertJsonPath('meta.total', 1);
    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/follow-ups?overdue=1')
        ->assertOk()->assertJsonPath('meta.total', 2);
});

it('forbids an uninvolved staff member from scheduling a follow-up', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    $lead = Lead::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->postJson("/api/v1/leads/{$lead->id}/follow-ups", [
        'due_at' => '2026-07-01 10:00:00',
    ])->assertForbidden();
});

it('404s on another tenant\'s follow-up', function (): void {
    $tenantA = makeTenant(['slug' => 'fu-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeTenant(['slug' => 'fu-b']);
    $lead = Lead::factory()->forTenant($tenantB)->create();
    $foreign = FollowUp::factory()->forTenant($tenantB)->attachedTo($lead)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson("/api/v1/follow-ups/{$foreign->id}/cancel")->assertNotFound();
});
