<?php

declare(strict_types=1);

use App\Modules\Operations\Models\Task;
use App\Modules\Organization\Models\Employee;

/**
 * @return array<string, mixed>
 */
function seedTwoNotificationsFor(): array
{
    $tenant = makeTenant();
    $user = makeUser($tenant, 'staff');
    $employee = Employee::factory()->forTenant($tenant)->create(['user_id' => $user->id]);
    $manager = makeUser($tenant, 'manager');
    $task = Task::factory()->forTenant($tenant)->create([
        'created_by' => $manager->id,
        'assignee_employee_id' => $employee->id,
    ]);
    clearTenantContext();

    test()->actingAs($manager, 'sanctum')
        ->putJson("/api/v1/tasks/{$task->id}/status", ['status' => 'in_progress'])->assertOk();
    test()->actingAs($manager, 'sanctum')
        ->postJson("/api/v1/tasks/{$task->id}/comments", ['body' => 'ping'])->assertCreated();

    return ['user' => $user, 'manager' => $manager];
}

it('lists notifications and reports the unread count', function (): void {
    ['user' => $user] = seedTwoNotificationsFor();

    $this->actingAs($user, 'sanctum')->getJson('/api/v1/notifications')
        ->assertOk()
        ->assertJsonPath('meta.total', 2)
        ->assertJsonStructure(['data' => [['id', 'type', 'data', 'read_at', 'created_at']]]);

    $this->actingAs($user, 'sanctum')->getJson('/api/v1/notifications/unread-count')
        ->assertOk()->assertJsonPath('data.unread', 2);
});

it('marks one notification read and then all', function (): void {
    ['user' => $user] = seedTwoNotificationsFor();

    $id = $this->actingAs($user, 'sanctum')->getJson('/api/v1/notifications?unread=1')->json('data.0.id');

    $this->actingAs($user, 'sanctum')->patchJson("/api/v1/notifications/{$id}/read")
        ->assertOk()->assertJsonPath('data.id', $id);

    $this->actingAs($user, 'sanctum')->getJson('/api/v1/notifications/unread-count')
        ->assertJsonPath('data.unread', 1);

    $this->actingAs($user, 'sanctum')->postJson('/api/v1/notifications/read-all')->assertOk();

    $this->actingAs($user, 'sanctum')->getJson('/api/v1/notifications/unread-count')
        ->assertJsonPath('data.unread', 0);
});

it('will not touch another user\'s notification', function (): void {
    ['user' => $user] = seedTwoNotificationsFor();
    $id = $this->actingAs($user, 'sanctum')->getJson('/api/v1/notifications')->json('data.0.id');

    $tenant = makeTenant(['slug' => 'notif-other']);
    $stranger = makeUser($tenant, 'staff');
    clearTenantContext();

    $this->actingAs($stranger, 'sanctum')->patchJson("/api/v1/notifications/{$id}/read")
        ->assertNotFound();
});

it('requires authentication', function (): void {
    $this->getJson('/api/v1/notifications')->assertUnauthorized();
});
