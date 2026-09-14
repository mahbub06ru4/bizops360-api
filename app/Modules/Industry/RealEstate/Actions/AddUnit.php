<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\UnitData;
use App\Modules\Industry\RealEstate\Domain\UnitStatus;
use App\Modules\Industry\RealEstate\Models\Building;
use App\Modules\Industry\RealEstate\Models\Unit;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class AddUnit
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Building $building, UnitData $data): Unit
    {
        $this->assertTenantOwns($building);

        if ($building->units()->where('unit_number', $data->unitNumber)->exists()) {
            throw ValidationException::withMessages([
                'unit_number' => 'This building already has a unit with that number.',
            ]);
        }

        $unit = new Unit($data->toAttributes());
        $unit->tenant_id = (int) $building->tenant_id;
        $unit->building_id = $building->getKey();
        $unit->status = UnitStatus::Available;
        $unit->save();

        return $unit;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
