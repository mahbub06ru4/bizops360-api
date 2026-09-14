<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\AmenityData;
use App\Modules\Industry\RealEstate\Models\Amenity;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Tenant\Context\TenantContext;

class AddAmenity
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(RealEstateProject $project, AmenityData $data): Amenity
    {
        $this->assertTenantOwns($project);

        $amenity = new Amenity($data->toAttributes());
        $amenity->tenant_id = (int) $project->tenant_id;
        $amenity->project_id = $project->getKey();
        $amenity->save();

        return $amenity;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
