<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions\Concerns;

use App\Modules\HR\Models\LeaveBalance;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\HR\Models\LeaveType;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

/**
 * Applies (or reverses) a leave request against the employee's balance for the
 * leave type and the year the leave starts in. Unpaid leave types are not
 * tracked. A balance row is created on demand from the type's default entitlement.
 */
trait AdjustsLeaveBalance
{
    abstract protected function tenantContext(): TenantContext;

    protected function applyLeaveToBalance(LeaveRequest $leaveRequest, int $delta): void
    {
        $leaveRequest->loadMissing('leaveType');

        /** @var LeaveType $type */
        $type = $leaveRequest->leaveType;

        if (! $type->is_paid) {
            return;
        }

        $balance = LeaveBalance::query()->firstOrNew([
            'employee_id' => $leaveRequest->employee_id,
            'leave_type_id' => $leaveRequest->leave_type_id,
            'year' => $leaveRequest->start_date->year,
        ]);

        if (! $balance->exists) {
            $balance->tenant_id = (int) $this->tenantContext()->tenant()->getKey();
            $balance->entitled_days = $type->default_days_per_year;
            $balance->used_days = 0;
        }

        $used = $balance->used_days + $delta;

        if ($delta > 0 && $used > $balance->entitled_days) {
            throw ValidationException::withMessages([
                'days' => "Insufficient {$type->name} balance: {$balance->remaining_days} day(s) remaining.",
            ]);
        }

        $balance->used_days = max(0, $used);
        $balance->save();
    }
}
