<?php

declare(strict_types=1);

use App\Modules\Operations\Models\Task;
use App\Modules\Operations\Models\TaskActivity;
use App\Modules\Organization\Models\Employee;

it('logs created, status and assignment activities for a task', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $employee = Employee::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum');

    $taskId = $this->postJson('/api/v1/tasks', ['title' => 'Track me'])
        ->assertCreated()->json('data.id');

    $this->putJson("/api/v1/tasks/{$taskId}/status", ['status' => 'in_progress'])->assertOk();
    $this->putJson("/api/v1/tasks/{$taskId}/assignee", [
        'assignee_employee_id' => $employee->id,
        'assignee_team_id' => null,
    ])->assertOk();
    $this->putJson("/api/v1/tasks/{$taskId}", ['title' => 'Renamed'])->assertOk();

    $events = $this->getJson("/api/v1/tasks/{$taskId}/activities")
        ->assertOk()
        ->json('data.*.event');

    expect($events)->toContain('created', 'status_changed', 'assigned', 'updated');

    $created = TaskActivity::withoutGlobalScopes()
        ->where('task_id', $taskId)->where('event', 'created')->firstOrFail();
    expect($created->causer_id)->toBe($manager->id);

    $statusChange = TaskActivity::withoutGlobalScopes()
        ->where('task_id', $taskId)->where('event', 'status_changed')->firstOrFail();
    expect($statusChange->properties)->toMatchArray(['from' => 'todo', 'to' => 'in_progress']);
});

it('hides another task\'s activity behind the view policy', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $outsider = makeUser($tenant, 'staff');
    $task = Task::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson("/api/v1/tasks/{$task->id}/activities")->assertOk();
    $this->actingAs($outsider, 'sanctum')->getJson("/api/v1/tasks/{$task->id}/activities")->assertForbidden();
});

it('404s activity for another tenant\'s task', function (): void {
    $tenantA = makeTenant(['slug' => 'act-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeTenant(['slug' => 'act-b']);
    $foreign = Task::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson("/api/v1/tasks/{$foreign->id}/activities")->assertNotFound();
});
