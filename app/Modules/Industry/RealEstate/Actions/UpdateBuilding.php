<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\BuildingData;
use App\Modules\Industry\RealEstate\Models\Building;
use App\Modules\Tenant\Context\TenantContext;

class UpdateBuilding
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Building $building, BuildingData $data): Building
    {
        $this->assertTenantOwns($building);

        $building->fill($data->toAttributes())->save();

        return $building->refresh();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
