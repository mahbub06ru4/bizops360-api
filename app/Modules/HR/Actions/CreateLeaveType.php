<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions;

use App\Modules\HR\Actions\Concerns\InteractsWithTenant;
use App\Modules\HR\Data\LeaveTypeData;
use App\Modules\HR\Models\LeaveType;
use App\Modules\Tenant\Context\TenantContext;

class CreateLeaveType
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(LeaveTypeData $data): LeaveType
    {
        $leaveType = new LeaveType($data->toAttributes());
        $leaveType->tenant_id = $this->currentTenantId();
        $leaveType->save();

        return $leaveType;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
