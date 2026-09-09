<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions;

use App\Models\User;
use App\Modules\Operations\Actions\Concerns\InteractsWithTenant;
use App\Modules\Operations\Actions\Concerns\NotifiesTaskParticipants;
use App\Modules\Operations\Data\TaskStatusData;
use App\Modules\Operations\Models\Task;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;

/**
 * Moves a task to a new status, maintaining `completed_at` (set when the task
 * becomes done, cleared when it leaves done), and notifies the task's people.
 */
class ChangeTaskStatus
{
    use InteractsWithTenant;
    use NotifiesTaskParticipants;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Task $task, TaskStatusData $data, User $actor): Task
    {
        $this->assertTenantOwns($task);

        $changed = $task->status !== $data->status;

        $task->status = $data->status;
        $task->completed_at = $data->status->isComplete()
            ? ($task->completed_at ?? Carbon::now())
            : null;
        $task->save();

        if ($changed) {
            $this->notifyTaskEvent(
                $task,
                'status_changed',
                "\"{$task->title}\" is now {$data->status->value}.",
                $actor,
            );
        }

        return $task->refresh()->load(['project', 'assigneeEmployee', 'assigneeTeam']);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
