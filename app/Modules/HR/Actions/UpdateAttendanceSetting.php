<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions;

use App\Modules\HR\Actions\Concerns\InteractsWithTenant;
use App\Modules\HR\Data\AttendanceSettingData;
use App\Modules\HR\Models\AttendanceSetting;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Creates or updates the current tenant's working-hours configuration.
 */
class UpdateAttendanceSetting
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(AttendanceSettingData $data): AttendanceSetting
    {
        $setting = AttendanceSetting::query()->firstOrNew([]);
        $setting->tenant_id = $this->currentTenantId();
        $setting->fill($data->toAttributes());
        $setting->save();

        return $setting->refresh();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
