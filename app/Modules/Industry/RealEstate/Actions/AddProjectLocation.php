<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\ProjectLocationData;
use App\Modules\Industry\RealEstate\Models\ProjectLocation;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Records where a project sits. A project usually has exactly one location
 * row; calling this again replaces it rather than accumulating duplicates.
 */
class AddProjectLocation
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(RealEstateProject $project, ProjectLocationData $data): ProjectLocation
    {
        $this->assertTenantOwns($project);

        $location = $project->locations()->first() ?? new ProjectLocation;
        $location->fill($data->toAttributes());
        $location->tenant_id = (int) $project->tenant_id;
        $location->project_id = $project->getKey();
        $location->save();

        return $location;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
