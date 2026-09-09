<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions;

use App\Models\User;
use App\Modules\Operations\Actions\Concerns\InteractsWithTenant;
use App\Modules\Operations\Data\TaskData;
use App\Modules\Operations\Domain\TaskStatus;
use App\Modules\Operations\Models\Project;
use App\Modules\Operations\Models\Task;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class CreateTask
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(TaskData $data, User $creator): Task
    {
        $this->assertReferenceOwned($data->projectId, Project::class);
        $this->assertParentIsAssignable($data->parentTaskId);

        $task = new Task($data->toAttributes());
        $task->tenant_id = $this->currentTenantId();
        $task->created_by = $creator->getKey();
        $task->status = TaskStatus::Todo;
        $task->save();

        return $task->load(['project', 'assigneeEmployee', 'assigneeTeam']);
    }

    private function assertParentIsAssignable(?int $parentTaskId): void
    {
        if ($parentTaskId === null) {
            return;
        }

        $this->assertReferenceOwned($parentTaskId, Task::class);

        /** @var Task $parent */
        $parent = Task::query()->findOrFail($parentTaskId);

        if ($parent->parent_task_id !== null) {
            throw ValidationException::withMessages([
                'parent_task_id' => 'A subtask cannot have its own subtasks.',
            ]);
        }
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
