<?php

declare(strict_types=1);

use App\Modules\CRM\Actions\SendFollowUpReminders;
use App\Modules\CRM\Models\FollowUp;
use App\Modules\CRM\Models\Lead;
use App\Modules\Organization\Models\Employee;
use Illuminate\Support\Carbon;

afterEach(fn () => Carbon::setTestNow());

it('reminds the assignee of a follow-up coming due and is idempotent', function (): void {
    Carbon::setTestNow('2026-06-15 09:00:00');
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $assignee = makeUser($tenant, 'staff');
    $assigneeEmp = Employee::factory()->forTenant($tenant)->create(['user_id' => $assignee->id]);
    $lead = Lead::factory()->forTenant($tenant)->create(['name' => 'Initech']);

    $due = FollowUp::factory()->forTenant($tenant)->attachedTo($lead)->create([
        'assigned_employee_id' => $assigneeEmp->id, 'due_at' => '2026-06-15 09:20:00',
    ]);
    $later = FollowUp::factory()->forTenant($tenant)->attachedTo($lead)->create([
        'assigned_employee_id' => $assigneeEmp->id, 'due_at' => '2026-06-15 11:00:00',
    ]);
    clearTenantContext();

    $sent = app(SendFollowUpReminders::class)->handle();

    expect($sent)->toBe(1)
        ->and($assignee->fresh()->notifications()->count())->toBe(1)
        ->and(FollowUp::withoutGlobalScopes()->find($due->id)->reminder_sent_at)->not->toBeNull()
        ->and(FollowUp::withoutGlobalScopes()->find($later->id)->reminder_sent_at)->toBeNull();

    $notification = $assignee->fresh()->notifications()->first();
    expect($notification->data['subject'])->toBe('Initech')
        ->and($notification->data['follow_up_id'])->toBe($due->id);

    expect(app(SendFollowUpReminders::class)->handle())->toBe(0);
});

it('falls back to the creator when no employee is assigned', function (): void {
    Carbon::setTestNow('2026-06-15 09:00:00');
    $tenant = makeTenant();
    $creator = makeUser($tenant, 'manager');
    $lead = Lead::factory()->forTenant($tenant)->create();
    FollowUp::factory()->forTenant($tenant)->attachedTo($lead)->create([
        'created_by' => $creator->id, 'assigned_employee_id' => null, 'due_at' => '2026-06-15 09:10:00',
    ]);
    clearTenantContext();

    app(SendFollowUpReminders::class)->handle();

    expect($creator->fresh()->notifications()->count())->toBe(1);
});

it('runs from the scheduled command', function (): void {
    Carbon::setTestNow('2026-06-15 09:00:00');
    $tenant = makeTenant();
    $creator = makeUser($tenant, 'manager');
    $lead = Lead::factory()->forTenant($tenant)->create();
    FollowUp::factory()->forTenant($tenant)->attachedTo($lead)->create([
        'created_by' => $creator->id, 'due_at' => '2026-06-15 09:15:00',
    ]);
    clearTenantContext();

    $this->artisan('crm:send-followup-reminders')->assertSuccessful();

    expect($creator->fresh()->notifications()->count())->toBe(1);
});
