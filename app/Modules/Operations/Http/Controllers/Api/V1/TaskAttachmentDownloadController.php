<?php

declare(strict_types=1);

namespace App\Modules\Operations\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Operations\Models\TaskAttachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a task attachment. Reached only via a short-lived signed URL (issued by
 * TaskAttachmentResource) plus the caller's Sanctum token and a re-check of the
 * parent task's view policy.
 */
class TaskAttachmentDownloadController extends Controller
{
    public function __invoke(TaskAttachment $taskAttachment): StreamedResponse
    {
        $task = $taskAttachment->task;

        if ($task === null) {
            abort(404);
        }

        $this->authorize('view', $task);

        return Storage::disk($taskAttachment->disk)->download(
            $taskAttachment->path,
            $taskAttachment->original_name,
        );
    }
}
