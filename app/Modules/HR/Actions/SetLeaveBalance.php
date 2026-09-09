<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions;

use App\Modules\HR\Actions\Concerns\InteractsWithTenant;
use App\Modules\HR\Data\LeaveBalanceData;
use App\Modules\HR\Models\LeaveBalance;
use App\Modules\HR\Models\LeaveType;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Creates or updates an employee's leave entitlement for a leave type and year.
 * Used days are preserved.
 */
class SetLeaveBalance
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(LeaveBalanceData $data): LeaveBalance
    {
        $this->assertReferenceOwned($data->employeeId, Employee::class);
        $this->assertReferenceOwned($data->leaveTypeId, LeaveType::class);

        $balance = LeaveBalance::query()->firstOrNew(
            [
                'employee_id' => $data->employeeId,
                'leave_type_id' => $data->leaveTypeId,
                'year' => $data->year,
            ],
            ['used_days' => 0],
        );

        $balance->tenant_id = $this->currentTenantId();
        $balance->entitled_days = $data->entitledDays;
        $balance->save();

        return $balance->fresh(['employee', 'leaveType']) ?? $balance;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
