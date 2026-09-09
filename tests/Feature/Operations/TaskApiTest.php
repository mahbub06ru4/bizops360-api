<?php

declare(strict_types=1);

use App\Modules\Operations\Models\Project;
use App\Modules\Operations\Models\Task;
use App\Modules\Organization\Models\Employee;

it('creates a standalone task, a project task, and a subtask', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $project = Project::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum');

    $taskId = $this->postJson('/api/v1/tasks', [
        'title' => 'Ship v1',
        'project_id' => $project->id,
        'priority' => 'high',
    ])->assertCreated()
        ->assertJsonPath('data.status', 'todo')
        ->assertJsonPath('data.priority', 'high')
        ->assertJsonPath('data.project_id', $project->id)
        ->assertJsonPath('data.created_by', $manager->id)
        ->json('data.id');

    $subId = $this->postJson('/api/v1/tasks', ['title' => 'Write tests', 'parent_task_id' => $taskId])
        ->assertCreated()
        ->assertJsonPath('data.parent_task_id', $taskId)
        ->json('data.id');

    $this->postJson('/api/v1/tasks', ['title' => 'Nested', 'parent_task_id' => $subId])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('parent_task_id');

    $this->getJson("/api/v1/tasks/{$taskId}")
        ->assertOk()
        ->assertJsonPath('data.subtasks_count', 1)
        ->assertJsonPath('data.subtasks.0.id', $subId);
});

it('filters the task list', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $project = Project::factory()->forTenant($tenant)->create();
    Task::factory()->forTenant($tenant)->create(['project_id' => $project->id, 'status' => 'done']);
    Task::factory()->forTenant($tenant)->create(['status' => 'todo']);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson("/api/v1/tasks?project_id={$project->id}")
        ->assertOk()->assertJsonPath('meta.total', 1);

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/tasks?status=todo')
        ->assertOk()->assertJsonPath('meta.total', 1);
});

it('scopes the task list to involvement unless the caller may view all', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $staff = makeUser($tenant, 'staff');
    $staffEmployee = Employee::factory()->forTenant($tenant)->create(['user_id' => $staff->id]);
    clearTenantContext();

    // staff-created
    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/tasks', ['title' => 'Mine'])->assertCreated();
    // assigned to staff's employee, created by manager
    $assigned = Task::factory()->forTenant($tenant)->create(['assignee_employee_id' => $staffEmployee->id]);
    // unrelated
    Task::factory()->forTenant($tenant)->create();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/tasks')
        ->assertOk()->assertJsonPath('meta.total', 2);

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/tasks')
        ->assertOk()->assertJsonPath('meta.total', 3);
});

it('rejects a project from another tenant', function (): void {
    $other = makeTenant(['slug' => 'task-o']);
    $foreignProject = Project::factory()->forTenant($other)->create();
    $tenant = makeTenant(['slug' => 'task-m']);
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson('/api/v1/tasks', [
        'title' => 'X', 'project_id' => $foreignProject->id,
    ])->assertUnprocessable()->assertJsonValidationErrors('project_id');
});

it('detaches subtasks when a parent is deleted', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $parent = Task::factory()->forTenant($tenant)->create();
    $child = Task::factory()->forTenant($tenant)->create(['parent_task_id' => $parent->id]);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->deleteJson("/api/v1/tasks/{$parent->id}")->assertNoContent();

    expect(Task::withoutGlobalScopes()->find($child->id)->parent_task_id)->toBeNull();
});

it('404s on another tenant\'s task', function (): void {
    $tenantA = makeTenant(['slug' => 'task-iso-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeTenant(['slug' => 'task-iso-b']);
    $foreign = Task::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson("/api/v1/tasks/{$foreign->id}")->assertNotFound();
});
