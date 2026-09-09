<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions;

use App\Modules\Operations\Actions\Concerns\InteractsWithTenant;
use App\Modules\Operations\Data\ProjectData;
use App\Modules\Operations\Models\Project;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;

class UpdateProject
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Project $project, ProjectData $data): Project
    {
        $this->assertTenantOwns($project);
        $this->assertReferenceOwned($data->departmentId, Department::class);
        $this->assertReferenceOwned($data->leadEmployeeId, Employee::class);

        $project->fill($data->toAttributes())->save();

        return $project->refresh()->load(['department', 'lead']);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
