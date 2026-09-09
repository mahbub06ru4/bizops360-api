<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions;

use App\Models\User;
use App\Modules\Operations\Actions\Concerns\InteractsWithTenant;
use App\Modules\Operations\Data\ProjectData;
use App\Modules\Operations\Models\Project;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;

class CreateProject
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(ProjectData $data, User $creator): Project
    {
        $this->assertReferenceOwned($data->departmentId, Department::class);
        $this->assertReferenceOwned($data->leadEmployeeId, Employee::class);

        $project = new Project($data->toAttributes());
        $project->tenant_id = $this->currentTenantId();
        $project->created_by = $creator->getKey();
        $project->save();

        return $project->load(['department', 'lead']);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
