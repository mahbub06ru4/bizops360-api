<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions;

use App\Modules\HR\Actions\Concerns\InteractsWithTenant;
use App\Modules\HR\Data\HolidayData;
use App\Modules\HR\Models\Holiday;
use App\Modules\Tenant\Context\TenantContext;

class UpdateHoliday
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Holiday $holiday, HolidayData $data): Holiday
    {
        $this->assertTenantOwns($holiday);

        $holiday->fill($data->toAttributes())->save();

        return $holiday->refresh();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
