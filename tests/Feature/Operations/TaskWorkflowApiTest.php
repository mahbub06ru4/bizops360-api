<?php

declare(strict_types=1);

use App\Modules\Operations\Models\Task;
use App\Modules\Organization\Models\Employee;
use App\Modules\Organization\Models\Team;

it('assigns and unassigns a task', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $employee = Employee::factory()->forTenant($tenant)->create();
    $team = Team::factory()->forTenant($tenant)->create();
    $task = Task::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->putJson("/api/v1/tasks/{$task->id}/assignee", [
        'assignee_employee_id' => $employee->id,
        'assignee_team_id' => $team->id,
    ])->assertOk()
        ->assertJsonPath('data.assignee_employee_id', $employee->id)
        ->assertJsonPath('data.assignee_team_id', $team->id);

    $this->actingAs($manager, 'sanctum')->putJson("/api/v1/tasks/{$task->id}/assignee", [
        'assignee_employee_id' => null,
        'assignee_team_id' => null,
    ])->assertOk()
        ->assertJsonPath('data.assignee_employee_id', null);
});

it('forbids staff from assigning tasks', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    $employee = Employee::factory()->forTenant($tenant)->create(['user_id' => $staff->id]);
    $task = Task::factory()->forTenant($tenant)->create(['created_by' => $staff->id]);
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->putJson("/api/v1/tasks/{$task->id}/assignee", [
        'assignee_employee_id' => $employee->id,
        'assignee_team_id' => null,
    ])->assertForbidden();
});

it('rejects assigning an employee from another tenant', function (): void {
    $other = makeTenant(['slug' => 'assign-o']);
    $foreign = Employee::factory()->forTenant($other)->create();
    $tenant = makeTenant(['slug' => 'assign-m']);
    $manager = makeUser($tenant, 'manager');
    $task = Task::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->putJson("/api/v1/tasks/{$task->id}/assignee", [
        'assignee_employee_id' => $foreign->id,
        'assignee_team_id' => null,
    ])->assertUnprocessable()->assertJsonValidationErrors('assignee_employee_id');
});

it('tracks completed_at as the task enters and leaves done', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $task = Task::factory()->forTenant($tenant)->create(['status' => 'in_progress']);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->putJson("/api/v1/tasks/{$task->id}/status", ['status' => 'done'])
        ->assertOk()
        ->assertJsonPath('data.status', 'done');
    expect(Task::withoutGlobalScopes()->find($task->id)->completed_at)->not->toBeNull();

    $this->actingAs($manager, 'sanctum')->putJson("/api/v1/tasks/{$task->id}/status", ['status' => 'in_progress'])
        ->assertOk();
    expect(Task::withoutGlobalScopes()->find($task->id)->completed_at)->toBeNull();
});

it('lets a staff creator change their own task status but not someone else\'s', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    $mine = Task::factory()->forTenant($tenant)->create(['created_by' => $staff->id]);
    $theirs = Task::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->putJson("/api/v1/tasks/{$mine->id}/status", ['status' => 'in_progress'])
        ->assertOk();

    $this->actingAs($staff, 'sanctum')->putJson("/api/v1/tasks/{$theirs->id}/status", ['status' => 'done'])
        ->assertForbidden();
});
