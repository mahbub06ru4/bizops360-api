<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions;

use App\Modules\Operations\Actions\Concerns\InteractsWithTenant;
use App\Modules\Operations\Data\TaskAssignmentData;
use App\Modules\Operations\Models\Task;
use App\Modules\Organization\Models\Employee;
use App\Modules\Organization\Models\Team;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Sets (or clears) a task's employee and team assignment.
 */
class AssignTask
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Task $task, TaskAssignmentData $data): Task
    {
        $this->assertTenantOwns($task);
        $this->assertReferenceOwned($data->employeeId, Employee::class);
        $this->assertReferenceOwned($data->teamId, Team::class);

        $task->assignee_employee_id = $data->employeeId;
        $task->assignee_team_id = $data->teamId;
        $task->save();

        return $task->load(['assigneeEmployee', 'assigneeTeam']);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
