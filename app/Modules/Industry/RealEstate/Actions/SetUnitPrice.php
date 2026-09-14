<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\UnitPriceData;
use App\Modules\Industry\RealEstate\Models\Unit;
use App\Modules\Industry\RealEstate\Models\UnitPrice;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Appends a new price point on a unit (kept as history rather than mutating
 * a single column — see {@see UnitPrice}).
 */
class SetUnitPrice
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Unit $unit, UnitPriceData $data): UnitPrice
    {
        $this->assertTenantOwns($unit);

        $price = new UnitPrice($data->toAttributes());
        $price->tenant_id = (int) $unit->tenant_id;
        $price->unit_id = $unit->getKey();
        $price->save();

        return $price;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
