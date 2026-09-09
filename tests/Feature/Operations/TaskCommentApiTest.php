<?php

declare(strict_types=1);

use App\Modules\Operations\Models\Task;
use App\Modules\Operations\Models\TaskActivity;
use App\Modules\Operations\Models\TaskComment;

it('adds, lists, edits and deletes a task comment', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $task = Task::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum');

    $id = $this->postJson("/api/v1/tasks/{$task->id}/comments", ['body' => 'Looks good'])
        ->assertCreated()
        ->assertJsonPath('data.body', 'Looks good')
        ->assertJsonPath('data.author_id', $manager->id)
        ->json('data.id');

    $this->getJson("/api/v1/tasks/{$task->id}/comments")
        ->assertOk()
        ->assertJsonPath('data.0.id', $id)
        ->assertJsonPath('meta.total', 1);

    $this->putJson("/api/v1/task-comments/{$id}", ['body' => 'Edited'])
        ->assertOk()->assertJsonPath('data.body', 'Edited');

    $this->deleteJson("/api/v1/task-comments/{$id}")->assertNoContent();
    expect(TaskComment::withoutGlobalScopes()->count())->toBe(0);
});

it('records a commented activity when a comment is added', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $task = Task::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')
        ->postJson("/api/v1/tasks/{$task->id}/comments", ['body' => 'x'])
        ->assertCreated();

    $activity = TaskActivity::withoutGlobalScopes()
        ->where('task_id', $task->id)->where('event', 'commented')->firstOrFail();

    expect($activity->causer_id)->toBe($manager->id);
});

it('validates the comment body', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $task = Task::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')
        ->postJson("/api/v1/tasks/{$task->id}/comments", [])
        ->assertUnprocessable()->assertJsonValidationErrors('body');
});

it('lets only the author edit a comment but a manager may delete any', function (): void {
    $tenant = makeTenant();
    $author = makeUser($tenant, 'staff');
    $manager = makeUser($tenant, 'manager');
    $task = Task::factory()->forTenant($tenant)->create(['created_by' => $author->id]);
    clearTenantContext();

    $id = $this->actingAs($author, 'sanctum')
        ->postJson("/api/v1/tasks/{$task->id}/comments", ['body' => 'mine'])
        ->assertCreated()->json('data.id');

    $this->actingAs($manager, 'sanctum')->putJson("/api/v1/task-comments/{$id}", ['body' => 'x'])
        ->assertForbidden();

    $this->actingAs($manager, 'sanctum')->deleteJson("/api/v1/task-comments/{$id}")
        ->assertNoContent();
});

it('404s commenting on another tenant\'s task', function (): void {
    $tenantA = makeTenant(['slug' => 'cmt-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeTenant(['slug' => 'cmt-b']);
    $foreign = Task::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')
        ->postJson("/api/v1/tasks/{$foreign->id}/comments", ['body' => 'x'])
        ->assertNotFound();
});

it('hides a task\'s comments from an uninvolved staff member', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $outsider = makeUser($tenant, 'staff');
    $task = Task::factory()->forTenant($tenant)->create();
    TaskComment::factory()->forTenant($tenant)->create(['task_id' => $task->id]);
    clearTenantContext();

    $this->actingAs($outsider, 'sanctum')->getJson("/api/v1/tasks/{$task->id}/comments")
        ->assertForbidden();
});
