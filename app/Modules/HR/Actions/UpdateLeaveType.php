<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions;

use App\Modules\HR\Actions\Concerns\InteractsWithTenant;
use App\Modules\HR\Data\LeaveTypeData;
use App\Modules\HR\Models\LeaveType;
use App\Modules\Tenant\Context\TenantContext;

class UpdateLeaveType
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(LeaveType $leaveType, LeaveTypeData $data): LeaveType
    {
        $this->assertTenantOwns($leaveType);

        $leaveType->fill($data->toAttributes())->save();

        return $leaveType->refresh();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
