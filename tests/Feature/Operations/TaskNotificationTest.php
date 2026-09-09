<?php

declare(strict_types=1);

use App\Modules\Operations\Models\Task;
use App\Modules\Organization\Models\Employee;

it('notifies a newly assigned employee, not the assigner', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $assignee = makeUser($tenant, 'staff');
    $assigneeEmp = Employee::factory()->forTenant($tenant)->create(['user_id' => $assignee->id]);
    $task = Task::factory()->forTenant($tenant)->create(['created_by' => $manager->id]);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->putJson("/api/v1/tasks/{$task->id}/assignee", [
        'assignee_employee_id' => $assigneeEmp->id,
        'assignee_team_id' => null,
    ])->assertOk();

    expect($assignee->fresh()->notifications()->count())->toBe(1)
        ->and($manager->fresh()->notifications()->count())->toBe(0);

    $notification = $assignee->fresh()->notifications()->first();
    expect($notification->data['event'])->toBe('assigned')
        ->and($notification->data['task_id'])->toBe($task->id)
        ->and($notification->data['actor_name'])->toBe($manager->name);
});

it('notifies the creator and assignee on a status change, never the actor', function (): void {
    $tenant = makeTenant();
    $creator = makeUser($tenant, 'staff');
    $assignee = makeUser($tenant, 'staff');
    $assigneeEmp = Employee::factory()->forTenant($tenant)->create(['user_id' => $assignee->id]);
    $mover = makeUser($tenant, 'manager');
    $task = Task::factory()->forTenant($tenant)->create([
        'created_by' => $creator->id,
        'assignee_employee_id' => $assigneeEmp->id,
        'status' => 'todo',
    ]);
    clearTenantContext();

    $this->actingAs($mover, 'sanctum')->putJson("/api/v1/tasks/{$task->id}/status", ['status' => 'done'])
        ->assertOk();

    expect($creator->fresh()->notifications()->count())->toBe(1)
        ->and($assignee->fresh()->notifications()->count())->toBe(1)
        ->and($mover->fresh()->notifications()->count())->toBe(0);
});

it('notifies the assignee when someone else comments', function (): void {
    $tenant = makeTenant();
    $creator = makeUser($tenant, 'manager');
    $assignee = makeUser($tenant, 'staff');
    $assigneeEmp = Employee::factory()->forTenant($tenant)->create(['user_id' => $assignee->id]);
    $task = Task::factory()->forTenant($tenant)->create([
        'created_by' => $creator->id,
        'assignee_employee_id' => $assigneeEmp->id,
    ]);
    clearTenantContext();

    $this->actingAs($creator, 'sanctum')->postJson("/api/v1/tasks/{$task->id}/comments", ['body' => 'thoughts?'])
        ->assertCreated();

    expect($assignee->fresh()->notifications()->count())->toBe(1)
        ->and($creator->fresh()->notifications()->count())->toBe(0);

    expect($assignee->fresh()->notifications()->first()->data['event'])->toBe('commented');
});

it('sends nothing when the only participant is the actor', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $task = Task::factory()->forTenant($tenant)->create(['created_by' => $manager->id]);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->putJson("/api/v1/tasks/{$task->id}/status", ['status' => 'in_progress'])
        ->assertOk();

    expect($manager->fresh()->notifications()->count())->toBe(0);
});
