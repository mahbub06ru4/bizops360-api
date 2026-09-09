<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions;

use App\Modules\Operations\Actions\Concerns\InteractsWithTenant;
use App\Modules\Operations\Models\TaskActivity;
use App\Modules\Operations\Models\TaskComment;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Facades\DB;

class DeleteTaskComment
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(TaskComment $comment): void
    {
        $this->assertTenantOwns($comment);

        DB::transaction(function () use ($comment): void {
            $taskId = (int) $comment->task_id;
            $tenantId = (int) $comment->tenant_id;

            $comment->delete();

            $activity = new TaskActivity([
                'event' => 'comment_deleted',
                'description' => 'Comment removed',
            ]);
            $activity->tenant_id = $tenantId;
            $activity->task_id = $taskId;
            $activity->save();
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
