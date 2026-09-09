<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions;

use App\Models\User;
use App\Modules\Operations\Actions\Concerns\InteractsWithTenant;
use App\Modules\Operations\Models\Task;
use App\Modules\Operations\Models\TaskActivity;
use App\Modules\Operations\Models\TaskAttachment;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Stores an uploaded file for a task on the configured disk and records the
 * metadata plus an `attachment_added` activity.
 */
class UploadTaskAttachment
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Task $task, UploadedFile $file, User $uploader): TaskAttachment
    {
        $this->assertTenantOwns($task);

        $tenantId = (int) $task->tenant_id;
        $disk = (string) config('filesystems.default');

        $path = $file->store("tenants/{$tenantId}/tasks/{$task->getKey()}/attachments", $disk);

        if (! is_string($path)) {
            throw ValidationException::withMessages(['file' => 'The attachment could not be stored.']);
        }

        return DB::transaction(function () use ($task, $file, $uploader, $tenantId, $disk, $path): TaskAttachment {
            $attachment = new TaskAttachment;
            $attachment->tenant_id = $tenantId;
            $attachment->task_id = (int) $task->getKey();
            $attachment->uploaded_by = $uploader->getKey();
            $attachment->disk = $disk;
            $attachment->path = $path;
            $attachment->original_name = $file->getClientOriginalName();
            $attachment->mime_type = $file->getClientMimeType();
            $attachment->size = (int) ($file->getSize() ?: 0);
            $attachment->save();

            $activity = new TaskActivity([
                'event' => 'attachment_added',
                'description' => "Attached {$attachment->original_name}",
                'properties' => ['attachment_id' => $attachment->getKey()],
            ]);
            $activity->tenant_id = $tenantId;
            $activity->task_id = (int) $task->getKey();
            $activity->causer_id = $uploader->getKey();
            $activity->save();

            return $attachment->load('uploader');
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
