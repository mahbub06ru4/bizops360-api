<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions;

use App\Modules\Operations\Actions\Concerns\InteractsWithTenant;
use App\Modules\Operations\Models\Task;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Deletes a task of the current tenant. Subtasks are detached (their
 * `parent_task_id` is nulled) by the database.
 */
class DeleteTask
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Task $task): void
    {
        $this->assertTenantOwns($task);

        $task->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
