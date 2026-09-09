<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions;

use App\Modules\Operations\Actions\Concerns\InteractsWithTenant;
use App\Modules\Operations\Data\TaskStatusData;
use App\Modules\Operations\Models\Task;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;

/**
 * Moves a task to a new status, maintaining `completed_at` (set when the task
 * becomes done, cleared when it leaves done).
 */
class ChangeTaskStatus
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Task $task, TaskStatusData $data): Task
    {
        $this->assertTenantOwns($task);

        $task->status = $data->status;
        $task->completed_at = $data->status->isComplete()
            ? ($task->completed_at ?? Carbon::now())
            : null;
        $task->save();

        return $task->refresh()->load(['project', 'assigneeEmployee', 'assigneeTeam']);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
