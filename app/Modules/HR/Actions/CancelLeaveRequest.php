<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions;

use App\Models\User;
use App\Modules\HR\Actions\Concerns\AdjustsLeaveBalance;
use App\Modules\HR\Actions\Concerns\InteractsWithTenant;
use App\Modules\HR\Domain\LeaveStatus;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Cancels a pending or approved leave request. An approved cancellation returns
 * the charged days to the employee's balance.
 */
class CancelLeaveRequest
{
    use AdjustsLeaveBalance;
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(LeaveRequest $leaveRequest, User $actor): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest, $actor): LeaveRequest {
            $this->assertTenantOwns($leaveRequest);

            if (! in_array($leaveRequest->status, [LeaveStatus::Pending, LeaveStatus::Approved], true)) {
                throw ValidationException::withMessages([
                    'status' => 'This leave request can no longer be cancelled.',
                ]);
            }

            $wasApproved = $leaveRequest->status->isApproved();

            $leaveRequest->status = LeaveStatus::Cancelled;
            $leaveRequest->decided_by = $actor->getKey();
            $leaveRequest->decided_at = Carbon::now();
            $leaveRequest->save();

            if ($wasApproved) {
                $this->applyLeaveToBalance($leaveRequest, -$leaveRequest->days);
            }

            return $leaveRequest->load(['employee', 'leaveType']);
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
