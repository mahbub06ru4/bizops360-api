<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions;

use App\Models\User;
use App\Modules\HR\Actions\Concerns\InteractsWithTenant;
use App\Modules\HR\Data\LeaveDecisionData;
use App\Modules\HR\Domain\LeaveStatus;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class RejectLeaveRequest
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(LeaveRequest $leaveRequest, User $approver, LeaveDecisionData $data): LeaveRequest
    {
        $this->assertTenantOwns($leaveRequest);

        if (! $leaveRequest->status->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Only a pending leave request can be rejected.',
            ]);
        }

        $leaveRequest->status = LeaveStatus::Rejected;
        $leaveRequest->decided_by = $approver->getKey();
        $leaveRequest->decided_at = Carbon::now();
        $leaveRequest->decision_note = $data->note;
        $leaveRequest->save();

        return $leaveRequest->load(['employee', 'leaveType', 'decider']);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
