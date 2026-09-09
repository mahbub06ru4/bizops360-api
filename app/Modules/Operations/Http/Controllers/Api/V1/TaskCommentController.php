<?php

declare(strict_types=1);

namespace App\Modules\Operations\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Operations\Actions\AddTaskComment;
use App\Modules\Operations\Actions\DeleteTaskComment;
use App\Modules\Operations\Actions\UpdateTaskComment;
use App\Modules\Operations\Http\Requests\TaskCommentRequest;
use App\Modules\Operations\Http\Resources\TaskCommentResource;
use App\Modules\Operations\Models\Task;
use App\Modules\Operations\Models\TaskComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TaskCommentController extends Controller
{
    public function index(Task $task): AnonymousResourceCollection
    {
        $this->authorize('view', $task);

        return TaskCommentResource::collection(
            $task->comments()->with('author')->latest()->paginate(),
        );
    }

    public function store(Task $task, TaskCommentRequest $request, AddTaskComment $action): JsonResponse
    {
        $this->authorize('view', $task);

        /** @var User $user */
        $user = $request->user();

        return TaskCommentResource::make($action->handle($task, $request->toData(), $user))
            ->response()->setStatusCode(201);
    }

    public function update(TaskComment $taskComment, TaskCommentRequest $request, UpdateTaskComment $action): TaskCommentResource
    {
        $this->authorize('update', $taskComment);

        return TaskCommentResource::make($action->handle($taskComment, $request->toData()));
    }

    public function destroy(TaskComment $taskComment, DeleteTaskComment $action): Response
    {
        $this->authorize('delete', $taskComment);

        $action->handle($taskComment);

        return response()->noContent();
    }
}
