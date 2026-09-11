<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions;

use App\Modules\HR\Actions\Concerns\InteractsWithTenant;
use App\Modules\HR\Data\OfficeLocationData;
use App\Modules\HR\Models\AttendanceSetting;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Creates or updates the current tenant's office location: the geofence
 * (label / coordinates / radius) and the attendance opening/closing window
 * used to tag check-ins as on-time or late. Shares the one-row-per-tenant
 * {@see AttendanceSetting} table with the work-schedule configuration.
 */
class UpdateOfficeLocation
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(OfficeLocationData $data): AttendanceSetting
    {
        $tenantId = $this->currentTenantId();

        $setting = AttendanceSetting::query()->where('tenant_id', $tenantId)->first()
            ?? new AttendanceSetting;

        $setting->tenant_id = $tenantId;
        $setting->fill($data->toAttributes());
        $setting->save();

        return $setting;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
