<?php

declare(strict_types=1);

namespace App\Modules\Operations\Observers;

use App\Modules\Operations\Domain\TaskStatus;
use App\Modules\Operations\Models\Task;
use App\Modules\Operations\Models\TaskActivity;
use Illuminate\Support\Facades\Auth;

/**
 * Writes an immutable {@see TaskActivity} row whenever a task is created or a
 * tracked field changes. The causer is the authenticated user, if any.
 */
class TaskObserver
{
    public function created(Task $task): void
    {
        $this->record($task, 'created', 'Task created');
    }

    public function updated(Task $task): void
    {
        if ($task->wasChanged('status')) {
            $from = $task->getOriginal('status');
            $from = $from instanceof TaskStatus ? $from->value : (string) $from;

            $this->record($task, 'status_changed', "Status changed to {$task->status->value}", [
                'from' => $from,
                'to' => $task->status->value,
            ]);
        }

        if ($task->wasChanged('assignee_employee_id') || $task->wasChanged('assignee_team_id')) {
            $this->record($task, 'assigned', 'Assignment updated', [
                'assignee_employee_id' => $task->assignee_employee_id,
                'assignee_team_id' => $task->assignee_team_id,
            ]);
        }

        $details = ['title', 'description', 'priority', 'due_at', 'project_id', 'parent_task_id'];

        if (array_intersect($details, array_keys($task->getChanges())) !== []) {
            $this->record($task, 'updated', 'Task details updated');
        }
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function record(Task $task, string $event, string $description, array $properties = []): void
    {
        $activity = new TaskActivity([
            'event' => $event,
            'description' => $description,
            'properties' => $properties === [] ? null : $properties,
        ]);
        $causerId = Auth::id();

        $activity->tenant_id = (int) $task->tenant_id;
        $activity->task_id = (int) $task->getKey();
        $activity->causer_id = is_numeric($causerId) ? (int) $causerId : null;
        $activity->save();
    }
}
