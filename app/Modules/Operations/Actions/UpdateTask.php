<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions;

use App\Modules\Operations\Actions\Concerns\InteractsWithTenant;
use App\Modules\Operations\Data\TaskData;
use App\Modules\Operations\Models\Project;
use App\Modules\Operations\Models\Task;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class UpdateTask
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Task $task, TaskData $data): Task
    {
        $this->assertTenantOwns($task);
        $this->assertReferenceOwned($data->projectId, Project::class);

        if ($data->parentTaskId !== null) {
            if ($data->parentTaskId === $task->getKey()) {
                throw ValidationException::withMessages(['parent_task_id' => 'A task cannot be its own parent.']);
            }
            $this->assertReferenceOwned($data->parentTaskId, Task::class);
        }

        $task->fill($data->toAttributes())->save();

        return $task->refresh()->load(['project', 'assigneeEmployee', 'assigneeTeam']);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
