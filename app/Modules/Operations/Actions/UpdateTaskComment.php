<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions;

use App\Modules\Operations\Actions\Concerns\InteractsWithTenant;
use App\Modules\Operations\Data\TaskCommentData;
use App\Modules\Operations\Models\TaskComment;
use App\Modules\Tenant\Context\TenantContext;

class UpdateTaskComment
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(TaskComment $comment, TaskCommentData $data): TaskComment
    {
        $this->assertTenantOwns($comment);

        $comment->body = $data->body;
        $comment->save();

        return $comment->load('author');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
