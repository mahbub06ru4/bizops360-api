<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\ProjectData;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class UpdateProject
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(RealEstateProject $project, ProjectData $data): RealEstateProject
    {
        $this->assertTenantOwns($project);

        if (! $project->status->isEditable()) {
            throw ValidationException::withMessages([
                'project' => "A {$project->status->value} project can no longer be edited.",
            ]);
        }

        $project->fill($data->toAttributes())->save();

        return $project->refresh();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
