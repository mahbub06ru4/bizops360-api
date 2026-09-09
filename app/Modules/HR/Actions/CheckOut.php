<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions;

use App\Models\User;
use App\Modules\HR\Actions\Concerns\InteractsWithTenant;
use App\Modules\HR\Actions\Concerns\ResolvesActingEmployee;
use App\Modules\HR\Actions\Concerns\ResolvesWorkSchedule;
use App\Modules\HR\Models\Attendance;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Records the acting user's check-out for today, computing worked minutes and
 * flagging an early leave against the tenant's work schedule.
 */
class CheckOut
{
    use InteractsWithTenant;
    use ResolvesActingEmployee;
    use ResolvesWorkSchedule;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(User $actor): Attendance
    {
        $employee = $this->employeeForUser($actor);

        $attendance = Attendance::query()
            ->where('employee_id', $employee->getKey())
            ->whereDate('date', Carbon::today()->toDateString())
            ->first();

        $checkIn = $attendance?->check_in_at;

        if ($attendance === null || $checkIn === null) {
            throw ValidationException::withMessages(['check_out' => 'You have not checked in today.']);
        }

        if ($attendance->check_out_at !== null) {
            throw ValidationException::withMessages(['check_out' => 'You have already checked out today.']);
        }

        $now = Carbon::now();
        $schedule = $this->workSchedule();

        $attendance->check_out_at = $now;
        $attendance->is_early_leave = $schedule->isEarlyLeave($now);
        $attendance->worked_minutes = $schedule->workedMinutes($checkIn, $now);
        $attendance->save();

        return $attendance->load('employee');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
