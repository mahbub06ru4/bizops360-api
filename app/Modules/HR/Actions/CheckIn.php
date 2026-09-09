<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions;

use App\Models\User;
use App\Modules\HR\Actions\Concerns\InteractsWithTenant;
use App\Modules\HR\Actions\Concerns\ResolvesActingEmployee;
use App\Modules\HR\Actions\Concerns\ResolvesWorkSchedule;
use App\Modules\HR\Domain\AttendanceStatus;
use App\Modules\HR\Models\Attendance;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Records the acting user's check-in for today, flagging lateness against the
 * tenant's work schedule.
 */
class CheckIn
{
    use InteractsWithTenant;
    use ResolvesActingEmployee;
    use ResolvesWorkSchedule;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(User $actor): Attendance
    {
        $employee = $this->employeeForUser($actor);
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::query()
            ->where('employee_id', $employee->getKey())
            ->whereDate('date', $today)
            ->first();

        if ($attendance?->check_in_at !== null) {
            throw ValidationException::withMessages(['check_in' => 'You have already checked in today.']);
        }

        $now = Carbon::now();
        $late = $this->workSchedule()->isLate($now);

        $attendance ??= new Attendance(['employee_id' => $employee->getKey(), 'date' => $today]);
        $attendance->tenant_id = $this->currentTenantId();
        $attendance->check_in_at = $now;
        $attendance->is_late = $late;
        $attendance->status = $late ? AttendanceStatus::Late : AttendanceStatus::Present;
        $attendance->save();

        return $attendance->load('employee');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
