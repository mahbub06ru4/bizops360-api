<?php

declare(strict_types=1);

namespace App\Modules\Operations\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Operations\Actions\AssignTask;
use App\Modules\Operations\Actions\ChangeTaskStatus;
use App\Modules\Operations\Actions\CreateTask;
use App\Modules\Operations\Actions\DeleteTask;
use App\Modules\Operations\Actions\UpdateTask;
use App\Modules\Operations\Http\Requests\AssignTaskRequest;
use App\Modules\Operations\Http\Requests\ChangeTaskStatusRequest;
use App\Modules\Operations\Http\Requests\TaskRequest;
use App\Modules\Operations\Http\Resources\TaskResource;
use App\Modules\Operations\Models\Task;
use App\Modules\Organization\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    /** @var list<string> */
    private const array WITH = ['project', 'assigneeEmployee', 'assigneeTeam'];

    /**
     * List tasks. Users without `task.view_all` see only tasks they created or
     * are assigned to. Filters: project_id, parent_task_id, status, priority,
     * assignee_employee_id, overdue.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Task::class);

        /** @var User $user */
        $user = $request->user();

        $query = Task::query()->with(self::WITH)->withCount('subtasks')->latest();

        foreach (['project_id', 'parent_task_id', 'status', 'priority', 'assignee_employee_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->string($filter)->toString());
            }
        }

        if ($request->boolean('overdue')) {
            $query->whereNotNull('due_at')->where('due_at', '<', now())
                ->whereNotIn('status', ['done', 'cancelled']);
        }

        if (! $user->can('task.view_all')) {
            $employeeId = Employee::query()->where('user_id', $user->getKey())->value('id');
            $query->where(function (Builder $q) use ($user, $employeeId): void {
                $q->where('created_by', $user->getKey());
                if ($employeeId !== null) {
                    $q->orWhere('assignee_employee_id', $employeeId);
                }
            });
        }

        return TaskResource::collection($query->paginate());
    }

    public function store(TaskRequest $request, CreateTask $action): JsonResponse
    {
        $this->authorize('create', Task::class);

        /** @var User $user */
        $user = $request->user();

        return TaskResource::make($action->handle($request->toData(), $user))
            ->response()->setStatusCode(201);
    }

    public function show(Task $task): TaskResource
    {
        $this->authorize('view', $task);

        return TaskResource::make(
            $task->load([...self::WITH, 'subtasks'])->loadCount('subtasks'),
        );
    }

    public function update(TaskRequest $request, Task $task, UpdateTask $action): TaskResource
    {
        $this->authorize('update', $task);

        return TaskResource::make($action->handle($task, $request->toData()));
    }

    public function assign(AssignTaskRequest $request, Task $task, AssignTask $action): TaskResource
    {
        $this->authorize('assign', $task);

        /** @var User $user */
        $user = $request->user();

        return TaskResource::make($action->handle($task, $request->toData(), $user));
    }

    public function changeStatus(ChangeTaskStatusRequest $request, Task $task, ChangeTaskStatus $action): TaskResource
    {
        $this->authorize('update', $task);

        /** @var User $user */
        $user = $request->user();

        return TaskResource::make($action->handle($task, $request->toData(), $user));
    }

    public function destroy(Task $task, DeleteTask $action): Response
    {
        $this->authorize('delete', $task);

        $action->handle($task);

        return response()->noContent();
    }
}
