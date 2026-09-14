<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\BuildingData;
use App\Modules\Industry\RealEstate\Models\Building;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Tenant\Context\TenantContext;

class AddBuilding
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(RealEstateProject $project, BuildingData $data): Building
    {
        $this->assertTenantOwns($project);

        $building = new Building($data->toAttributes());
        $building->tenant_id = (int) $project->tenant_id;
        $building->project_id = $project->getKey();
        $building->save();

        return $building;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
