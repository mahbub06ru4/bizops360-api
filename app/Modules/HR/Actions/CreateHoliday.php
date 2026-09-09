<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions;

use App\Modules\HR\Actions\Concerns\InteractsWithTenant;
use App\Modules\HR\Data\HolidayData;
use App\Modules\HR\Models\Holiday;
use App\Modules\Tenant\Context\TenantContext;

class CreateHoliday
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(HolidayData $data): Holiday
    {
        $holiday = new Holiday($data->toAttributes());
        $holiday->tenant_id = $this->currentTenantId();
        $holiday->save();

        return $holiday;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
