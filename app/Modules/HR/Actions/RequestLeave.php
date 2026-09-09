<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions;

use App\Models\User;
use App\Modules\HR\Actions\Concerns\AdjustsLeaveBalance;
use App\Modules\HR\Actions\Concerns\InteractsWithTenant;
use App\Modules\HR\Data\LeaveRequestData;
use App\Modules\HR\Domain\LeaveDayCalculator;
use App\Modules\HR\Domain\LeaveStatus;
use App\Modules\HR\Models\Holiday;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\HR\Models\LeaveType;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Files a leave request for an employee. A user without `leave.approve` may only
 * request leave for their own linked employee record. Auto-approves and charges
 * the balance immediately when the leave type does not require approval.
 */
class RequestLeave
{
    use AdjustsLeaveBalance;
    use InteractsWithTenant;

    public function __construct(
        private readonly TenantContext $context,
        private readonly LeaveDayCalculator $calculator,
    ) {}

    public function handle(LeaveRequestData $data, User $actor): LeaveRequest
    {
        return DB::transaction(function () use ($data, $actor): LeaveRequest {
            $employeeId = $this->resolveEmployeeId($data, $actor);

            $this->assertReferenceOwned($employeeId, Employee::class);
            $this->assertReferenceOwned($data->leaveTypeId, LeaveType::class);

            /** @var LeaveType $type */
            $type = LeaveType::query()->findOrFail($data->leaveTypeId);

            $start = Carbon::parse($data->startDate)->startOfDay();
            $end = Carbon::parse($data->endDate)->startOfDay();

            $holidays = Holiday::query()
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->get()
                ->map(fn (Holiday $holiday): string => $holiday->date->toDateString())
                ->values()
                ->all();

            $days = $this->calculator->countChargeableDays($start, $end, $holidays);

            if ($days < 1) {
                throw ValidationException::withMessages([
                    'start_date' => 'The selected range contains no chargeable days.',
                ]);
            }

            $status = $type->requires_approval ? LeaveStatus::Pending : LeaveStatus::Approved;

            $leaveRequest = new LeaveRequest([
                'employee_id' => $employeeId,
                'leave_type_id' => $data->leaveTypeId,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'days' => $days,
                'reason' => $data->reason,
            ]);
            $leaveRequest->tenant_id = $this->currentTenantId();
            $leaveRequest->status = $status;

            if ($status === LeaveStatus::Approved) {
                $leaveRequest->decided_at = Carbon::now();
            }

            $leaveRequest->save();

            if ($status === LeaveStatus::Approved) {
                $this->applyLeaveToBalance($leaveRequest, $leaveRequest->days);
            }

            return $leaveRequest->load(['employee', 'leaveType']);
        });
    }

    private function resolveEmployeeId(LeaveRequestData $data, User $actor): int
    {
        if ($actor->can('leave.approve') && $data->employeeId !== null) {
            return $data->employeeId;
        }

        $employee = Employee::query()->where('user_id', $actor->getKey())->first();

        if ($employee === null) {
            throw ValidationException::withMessages([
                'employee_id' => 'You have no employee record; specify an employee to request leave for.',
            ]);
        }

        return (int) $employee->getKey();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
