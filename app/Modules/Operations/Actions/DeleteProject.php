<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions;

use App\Modules\Operations\Actions\Concerns\InteractsWithTenant;
use App\Modules\Operations\Models\Project;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Deletes a project of the current tenant. Its tasks are detached (their
 * `project_id` is nulled) by the database.
 */
class DeleteProject
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Project $project): void
    {
        $this->assertTenantOwns($project);

        $project->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
