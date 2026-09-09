<?php

declare(strict_types=1);

use App\Modules\Operations\Models\Project;
use App\Modules\Operations\Models\Task;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Employee;
use Illuminate\Support\Carbon;

beforeEach(fn () => Carbon::setTestNow('2026-06-15 09:00:00'));
afterEach(fn () => Carbon::setTestNow());

it('returns the tenant task and project overview', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');

    Task::factory()->forTenant($tenant)->create(['status' => 'todo']);
    Task::factory()->forTenant($tenant)->create(['status' => 'in_progress', 'due_at' => '2026-06-14 10:00:00']);
    Task::factory()->forTenant($tenant)->create(['status' => 'todo', 'due_at' => '2026-06-15 17:00:00']);
    Task::factory()->forTenant($tenant)->create(['status' => 'done', 'completed_at' => '2026-06-15 08:00:00']);
    Project::factory()->forTenant($tenant)->create(['status' => 'active']);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/operations/overview')
        ->assertOk()
        ->assertJsonPath('data.tasks.open', 3)
        ->assertJsonPath('data.tasks.overdue', 1)
        ->assertJsonPath('data.tasks.due_today', 1)
        ->assertJsonPath('data.tasks.completed_this_week', 1)
        ->assertJsonPath('data.tasks.by_status.todo', 2)
        ->assertJsonPath('data.tasks.by_priority.normal', 4)
        ->assertJsonPath('data.projects.by_status.active', 1);
});

it('reports employee workload, busiest first', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $a = Employee::factory()->forTenant($tenant)->create(['first_name' => 'Aisha', 'last_name' => 'Khan']);
    $b = Employee::factory()->forTenant($tenant)->create();

    Task::factory()->count(3)->forTenant($tenant)->create(['assignee_employee_id' => $a->id, 'status' => 'todo']);
    Task::factory()->forTenant($tenant)->create([
        'assignee_employee_id' => $a->id, 'status' => 'in_progress', 'due_at' => '2026-06-14 10:00:00',
    ]);
    Task::factory()->forTenant($tenant)->create(['assignee_employee_id' => $a->id, 'status' => 'done']);
    Task::factory()->forTenant($tenant)->create(['assignee_employee_id' => $b->id, 'status' => 'todo']);
    clearTenantContext();

    $data = $this->actingAs($manager, 'sanctum')->getJson('/api/v1/operations/workload')
        ->assertOk()->json('data');

    expect($data[0]['employee_id'])->toBe($a->id)
        ->and($data[0]['employee_name'])->toBe('Aisha Khan')
        ->and($data[0]['open_tasks'])->toBe(4)
        ->and($data[0]['overdue_tasks'])->toBe(1)
        ->and($data[1]['employee_id'])->toBe($b->id)
        ->and($data[1]['open_tasks'])->toBe(1);
});

it('rolls up tasks per department', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $dept = Department::factory()->forTenant($tenant)->create(['name' => 'Engineering']);
    $project = Project::factory()->forTenant($tenant)->create(['department_id' => $dept->id]);

    Task::factory()->count(2)->forTenant($tenant)->create(['project_id' => $project->id, 'status' => 'todo']);
    Task::factory()->forTenant($tenant)->create(['project_id' => $project->id, 'status' => 'done']);
    Task::factory()->forTenant($tenant)->create([
        'project_id' => $project->id, 'status' => 'in_progress', 'due_at' => '2026-06-14 08:00:00',
    ]);

    $noDept = Project::factory()->forTenant($tenant)->create(['department_id' => null]);
    Task::factory()->forTenant($tenant)->create(['project_id' => $noDept->id]);
    clearTenantContext();

    $data = $this->actingAs($manager, 'sanctum')->getJson('/api/v1/operations/department-performance')
        ->assertOk()->json('data');

    expect($data)->toHaveCount(1)
        ->and($data[0]['department_id'])->toBe($dept->id)
        ->and($data[0]['department_name'])->toBe('Engineering')
        ->and($data[0]['projects'])->toBe(1)
        ->and($data[0]['total_tasks'])->toBe(4)
        ->and($data[0]['completed_tasks'])->toBe(1)
        ->and($data[0]['open_tasks'])->toBe(3)
        ->and($data[0]['overdue_tasks'])->toBe(1);
});

it('scopes the overview to the current tenant', function (): void {
    $tenantA = makeTenant(['slug' => 'ov-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeTenant(['slug' => 'ov-b']);
    Task::factory()->count(5)->forTenant($tenantB)->create(['status' => 'todo']);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/operations/overview')
        ->assertOk()->assertJsonPath('data.tasks.open', 0);
});

it('forbids staff from the operations dashboards', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/operations/overview')->assertForbidden();
    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/operations/workload')->assertForbidden();
    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/operations/department-performance')->assertForbidden();
});
