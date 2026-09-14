<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\UnitData;
use App\Modules\Industry\RealEstate\Models\Unit;
use App\Modules\Tenant\Context\TenantContext;

class UpdateUnit
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Unit $unit, UnitData $data): Unit
    {
        $this->assertTenantOwns($unit);

        $unit->fill($data->toAttributes())->save();

        return $unit->refresh();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
