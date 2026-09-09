<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions;

use App\Modules\Operations\Actions\Concerns\InteractsWithTenant;
use App\Modules\Operations\Models\TaskActivity;
use App\Modules\Operations\Models\TaskAttachment;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteTaskAttachment
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(TaskAttachment $attachment): void
    {
        $this->assertTenantOwns($attachment);

        $disk = $attachment->disk;
        $path = $attachment->path;

        DB::transaction(function () use ($attachment): void {
            $tenantId = (int) $attachment->tenant_id;
            $taskId = (int) $attachment->task_id;
            $name = $attachment->original_name;

            $attachment->delete();

            $activity = new TaskActivity([
                'event' => 'attachment_removed',
                'description' => "Removed {$name}",
            ]);
            $activity->tenant_id = $tenantId;
            $activity->task_id = $taskId;
            $activity->save();
        });

        Storage::disk($disk)->delete($path);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
