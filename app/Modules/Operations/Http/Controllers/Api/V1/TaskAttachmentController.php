<?php

declare(strict_types=1);

namespace App\Modules\Operations\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Operations\Actions\DeleteTaskAttachment;
use App\Modules\Operations\Actions\UploadTaskAttachment;
use App\Modules\Operations\Http\Requests\UploadTaskAttachmentRequest;
use App\Modules\Operations\Http\Resources\TaskAttachmentResource;
use App\Modules\Operations\Models\Task;
use App\Modules\Operations\Models\TaskAttachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;

class TaskAttachmentController extends Controller
{
    public function index(Task $task): AnonymousResourceCollection
    {
        $this->authorize('view', $task);

        return TaskAttachmentResource::collection(
            $task->attachments()->with('uploader')->latest()->paginate(),
        );
    }

    public function store(Task $task, UploadTaskAttachmentRequest $request, UploadTaskAttachment $action): JsonResponse
    {
        $this->authorize('view', $task);

        /** @var User $user */
        $user = $request->user();

        /** @var UploadedFile $file */
        $file = $request->file('file');

        return TaskAttachmentResource::make($action->handle($task, $file, $user))
            ->response()->setStatusCode(201);
    }

    public function destroy(TaskAttachment $taskAttachment, DeleteTaskAttachment $action): Response
    {
        $this->authorize('delete', $taskAttachment);

        $action->handle($taskAttachment);

        return response()->noContent();
    }
}
