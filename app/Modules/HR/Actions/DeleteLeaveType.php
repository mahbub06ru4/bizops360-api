<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions;

use App\Modules\HR\Actions\Concerns\InteractsWithTenant;
use App\Modules\HR\Models\LeaveType;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class DeleteLeaveType
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(LeaveType $leaveType): void
    {
        $this->assertTenantOwns($leaveType);

        if ($leaveType->leaveRequests()->exists()) {
            throw ValidationException::withMessages([
                'leave_type' => 'This leave type has leave requests and cannot be deleted.',
            ]);
        }

        $leaveType->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
