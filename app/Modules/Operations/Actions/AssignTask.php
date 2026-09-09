<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions;

use App\Models\User;
use App\Modules\Operations\Actions\Concerns\InteractsWithTenant;
use App\Modules\Operations\Actions\Concerns\NotifiesTaskParticipants;
use App\Modules\Operations\Data\TaskAssignmentData;
use App\Modules\Operations\Models\Task;
use App\Modules\Organization\Models\Employee;
use App\Modules\Organization\Models\Team;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Sets (or clears) a task's employee and team assignment, notifying a newly
 * assigned employee.
 */
class AssignTask
{
    use InteractsWithTenant;
    use NotifiesTaskParticipants;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Task $task, TaskAssignmentData $data, User $actor): Task
    {
        $this->assertTenantOwns($task);
        $this->assertReferenceOwned($data->employeeId, Employee::class);
        $this->assertReferenceOwned($data->teamId, Team::class);

        $previousEmployeeId = $task->assignee_employee_id;

        $task->assignee_employee_id = $data->employeeId;
        $task->assignee_team_id = $data->teamId;
        $task->save();

        if ($data->employeeId !== null && $data->employeeId !== $previousEmployeeId) {
            $this->notifyTaskEvent(
                $task,
                'assigned',
                "You were assigned to \"{$task->title}\".",
                $actor,
                $data->employeeId,
            );
        }

        return $task->load(['assigneeEmployee', 'assigneeTeam']);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
